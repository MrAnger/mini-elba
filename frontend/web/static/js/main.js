(function ($) {
    $(document).ready(function () {
        $.format.locale({
            number: {
                groupingSeparator: ' ',
                decimalSeparator: ','
            }
        });

        // Скрипт динамичего добавления позиций счета
        $('.js-add-item').click(function (e) {
            e.preventDefault();

            var $el = $(this),
                $itemsHolder = $('.js-items-holder'),
                itemHtml = $('#item-layout').html();

            // Получаем допустимый id для новой позиции
            var id = -1;
            while ($itemsHolder.find('.item[data-id=' + id + ']').length > 0) {
                id--;
            }

            // Проставляем полученный id
            itemHtml = itemHtml.replace(/tIDt/ig, id);

            $itemsHolder.append($(itemHtml));
        });

        $(document).on('click', '.js-item-delete', function (e) {
            e.preventDefault();

            var $el = $(this),
                $item = $el.parents('.item');

            if (confirm("Вы уверены, что хотите удалить эту позицию?")) {
                $item.remove();
            }
        });

        // Скрипт автоматичесского расчета общей суммы счета
        function calculateInvoiceSummary() {
            var $summaries = $('.js-input-summary'),
                summary = 0,
                $summaryHolder = $('.js-summary');

            $summaries.each(function (key, input) {
                var value = parseFloat($(input).val());

                if (value) {
                    summary += value;
                }
            });

            $summaryHolder.html($.format.number(summary, '#,##0.00#'));
        }

        calculateInvoiceSummary();

        $(document).on('keyup', '.js-input-summary', function (e) {
            var $item = $(this).parents('.item'),
                $quantity = $item.find('.js-input-quantity'),
                $price = $item.find('.js-input-price'),
                $summary = $item.find('.js-input-summary');

            // Исправляем запятые на точки
            if ($summary.val().length > 0) {
                $summary.val($summary.val().replace(/,/ig, '.'));
            }

            var q = parseFloat($quantity.val()),
                p = parseFloat($price.val()),
                s = parseFloat($summary.val());

            if (q && p && s) {
                $price.val((s / q).toFixed(2));
            }

            calculateInvoiceSummary();
        });

        // Скрипт автоматического расчета суммы позиции счета при изменении кол-ва или стоимости за единицу
        $(document).on('keyup change', '.js-input-quantity, .js-input-price', function (e) {
            var $item = $(this).parents('.item'),
                $quantity = $item.find('.js-input-quantity'),
                $price = $item.find('.js-input-price'),
                $summary = $item.find('.js-input-summary');

            // Исправляем запятые на точки
            if ($quantity.val().length > 0) {
                $quantity.val($quantity.val().replace(/,/ig, '.'));
            }

            if ($price.val().length > 0) {
                $price.val($price.val().replace(/,/ig, '.'));
            }

            var q = parseFloat($quantity.val()),
                p = parseFloat($price.val());

            if (q && p) {
                $summary.val((q * p).toFixed(2));

                calculateInvoiceSummary();
            }
        });

        // Скрипт запуска модального окна для изменения баланса позиции счета
        $(document).on('click', '.js-change-item-paid', function (e) {
            e.preventDefault();

            var $el = $(this),
                url = $el.attr('href');

            $.get(url, function (response) {
                var $modal = $('#modal-change-invoice-item-paid'),
                    $form = $modal.find('form');

                $form.data('item-id', response.item.id);
                $form.data('available-sum', response.availableSum);

                $form.find('.js-item-name').html(response.item.name);
                $form.find('.js-item-summary').html(response.formattedSummary);
                $form.find('.js-input-paid').data('max', response.availableSum);
                $form.find('.js-item-id').val(response.item.id);
                $form.find('.js-input-paid')
                    .attr('placeholder', $.format.number(response.availableSum, '#,##0.00#'))
                    .val('');

                $form.yiiActiveForm('resetForm');

                $modal.modal('show');
            });
        });

        $(document).on('beforeSubmit', '#modal-change-invoice-item-paid form', function (e) {
            var $form = $(this),
                $modal = $form.parents('#modal-change-invoice-item-paid');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    if (response) {
                        $.pjax({
                            url: window.location.href,
                            container: $('#invoice-item-list'),
                            scrollTo: false,
                            timeout: 8000
                        });

                        $modal.modal('hide');
                    }
                },
                error: function (e) {
                    alert('Произошла ошибка при отправке запроса. Пожалуйста, попробуйте позже.');
                }
            });

            return false;
        });

        // Скрипт запуска модального окна для линковки поступлений со счетами
        $(document).on('click', '.js-link-payment-to-invoice', function (e) {
            e.preventDefault();

            var $el = $(this),
                url = $el.attr('href');

            $.get(url, function (response) {
                var $modal = $('#modal-link-payment-to-invoice'),
                    $form = $modal.find('form');

                $form.data('payment-id', response.payment.id);
                $form.data('available-sum', response.availableSum);
                $form.data('payment', response.payment);
                $form.data('invoiceList', response.invoiceList);

                $form.find('.js-payment-id').val(response.payment.id);

                $form.yiiActiveForm('resetForm');

                new PaymentLinkToInvoiceForm($form);

                $modal.modal('show');
            });
        });

        function PaymentLinkToInvoiceForm($form) {
            var self = this,
                availableSum = $form.data('available-sum'),
                payment = $form.data('payment'),
                invoiceList = $form.data('invoice-list'),
                $sumLeft = $form.find('.js-total-sum-left'),
                $invoicesHolder = $form.find('.js-invoices-holder'),
                $invoiceTemplate = $form.find('.js-invoice-template');

            this.printInvoices = function () {
                $.each(invoiceList, function (index, invoice) {
                    var $item = $($invoiceTemplate.html());

                    $item.attr('data-id', invoice.id);
                    $item.data('invoice', invoice);

                    var $checkBox = $item.find('.js-checked');

                    $checkBox.prop('checked', (invoice.linked) ? true : false);
                    $item.find('.js-name').html(invoice.name);

                    var sumLeft = parseFloat(invoice.summary) - parseFloat(invoice.total_paid),
                        $sumLeft = $item.find('.js-sum-left');

                    if (sumLeft > 0) {
                        $sumLeft.show().find('.value').html("Осталось связать: " + $.format.number(sumLeft, '#,##0.00#'));
                    } else {
                        $sumLeft.hide();
                    }

                    var sumLinked = parseFloat(invoice.linked_sum),
                        $linkedSumInput = $item.find('.js-input-sum'),
                        $linkedSumFake = $item.find('.js-fake-input-sum');

                    $linkedSumInput.val(sumLinked);
                    $linkedSumFake.html($.format.number(sumLinked, '#,##0.00#'));

                    $linkedSumInput.change(function (e) {
                        $linkedSumFake.html($.format.number(parseFloat($(this).val()), '#,##0.00#'));
                    });

                    var $summary = $item.find('.js-invoice-summary');
                    $summary.html($.format.number(parseFloat(invoice.summary), '#,##0.00#'));

                    if ($checkBox.prop('checked')) {
                        $linkedSumInput.show();
                        $linkedSumFake.hide();
                    } else {
                        $linkedSumInput.hide();
                        $linkedSumFake.show();
                    }

                    $checkBox.change(function () {
                        if ($(this).prop('checked')) {
                            $linkedSumInput.show();
                            $linkedSumFake.hide();
                        } else {
                            $linkedSumInput.hide();
                            $linkedSumFake.show();
                        }
                    });

                    $invoicesHolder.append($item);
                });
            };

            this.showSumLeft = function () {
                if (availableSum == 0) {
                    $sumLeft.html("<div class='text-success'>Связанна вся сумма</div>")
                } else {
                    $sumLeft.html("<div class='text-warning'>Осталось связать: " + $.format.number(parseFloat(availableSum), '#,##0.00#') + "</div>");
                }
            };

            this._init = function () {
                $invoicesHolder.html('');

                self.showSumLeft();
                self.printInvoices();
            };

            self._init();
        }
    });
})(jQuery);