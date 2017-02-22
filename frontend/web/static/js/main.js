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
                    .val(response.availableSum);

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

                $form.data('payment', response.payment);
                $form.data('invoiceList', response.invoiceList);
                $form.data('update-pjax', $el.data('update-pjax'));

                $form.yiiActiveForm('resetForm');

                new PaymentLinkToInvoiceForm($form);

                $modal.modal('show');
            });
        });

        function PaymentLinkToInvoiceForm($form) {
            var self = this,
                $modal = $form.parents('.modal').eq(0),
                payment = $form.data('payment'),
                paymentSum = parseFloat(payment.income),
                invoiceList = $form.data('invoice-list'),
                $paymentDescriptionHolder = $form.find('.js-payment-description'),
                $sumLeftHolder = $form.find('.js-total-sum-left'),
                $invoicesHolder = $form.find('.js-invoices-holder'),
                $invoiceTemplate = $form.find('.js-invoice-template');

            this.printInvoices = function () {
                $.each(invoiceList, function (index, invoice) {
                    var $item = $($invoiceTemplate.html()),
                        $checkBoxInput = $item.find('.js-checked'),
                        $nameHolder = $item.find('.js-name'),
                        $sumLeftHolder = $item.find('.js-sum-left'),
                        $linkedSumInput = $item.find('.js-input-sum'),
                        $linkedSumFakeHolder = $item.find('.js-fake-input-sum'),
                        $summaryHolder = $item.find('.js-invoice-summary');

                    // Настраиваем первичное визуальное состояние
                    $item.attr('data-id', invoice.id);
                    $item.data('invoice', invoice);

                    $checkBoxInput.prop('checked', (invoice.linked) ? true : false);
                    $nameHolder.html(invoice.name);

                    // Настраиваем первичное отображения инпутов линкованной суммы
                    initLinkedSumInput();
                    // Отображаем оставшуюся сумму для связки
                    showInvoiceSumLeft();
                    // Отображаем общую сумму счета
                    $summaryHolder.html($.format.number(parseFloat(invoice.summary), '#,##0.00#'));
                    // Настраиваем поведение чекбокса
                    initCheckboxInput();

                    $invoicesHolder.append($item);

                    function getInvoiceSumLeft(withoutLinkedSumInputValue) {
                        var invoiceSummary = parseFloat(invoice.summary),
                            invoiceTotalPaid = parseFloat(invoice.total_paid),
                            linkedSum = getInputLinkedSumValue();

                        if (withoutLinkedSumInputValue === undefined)
                            withoutLinkedSumInputValue = false;

                        // Корректируем invoiceTotalPaid, если в этой сумме фигурирует линковка из данного поступления
                        if (invoice.linked) {
                            invoiceTotalPaid -= parseFloat(invoice.linked_sum);
                        }

                        if (withoutLinkedSumInputValue) {
                            return invoiceSummary - invoiceTotalPaid;
                        } else {
                            return invoiceSummary - invoiceTotalPaid - linkedSum;
                        }
                    }

                    function showInvoiceSumLeft() {
                        var sumLeft = getInvoiceSumLeft();

                        if (sumLeft > 0) {
                            $sumLeftHolder.removeClass('text-success')
                                .addClass('text-danger')
                                .find('.value').html("Осталось связать: " + $.format.number(sumLeft, '#,##0.00#'));
                        } else if (sumLeft < 0) {
                            $sumLeftHolder.removeClass('text-success')
                                .addClass('text-danger')
                                .find('.value').html("Переизбыток: " + $.format.number(sumLeft, '#,##0.00#'));
                        } else {
                            $sumLeftHolder.removeClass('text-danger')
                                .addClass('text-success')
                                .find('.value').html("Вся сумма связана");
                        }
                    }

                    function initLinkedSumInput() {
                        var sumLinked = parseFloat(invoice.linked_sum);

                        $linkedSumInput.val(sumLinked.toFixed(2));
                        $linkedSumFakeHolder.html($.format.number(sumLinked, '#,##0.00#'));

                        // Разрешаем ввод в инпут только цифр и точки
                        $linkedSumInput.on('keyup change', function (e) {
                            $linkedSumInput.val($linkedSumInput.val().replace(/[^0-9\.]/ig, ''));
                        });

                        // Вместо пустого значения прописываем 0
                        $linkedSumInput.on('change', function (e) {
                            if ($linkedSumInput.val().length == 0)
                                $linkedSumInput.val((0).toFixed(2));
                        });

                        // Связываем значение инпута и фейкового элемента
                        $linkedSumInput.change(function (e) {
                            var value = parseFloat($(this).val());

                            if (!value)
                                value = 0;

                            $linkedSumFakeHolder.html($.format.number(value, '#,##0.00#'));
                        });

                        // Вызываем пересчет отображаемых сумм остатков
                        $linkedSumInput.on('keyup change', function (e) {
                            showInvoiceSumLeft();
                            self.showPaymentSumLeft();
                        });

                        // Задаём начальные параметры видимости исходя из состояния чекбокса
                        if ($checkBoxInput.prop('checked')) {
                            $linkedSumInput.show();
                            $linkedSumFakeHolder.hide();
                        } else {
                            $linkedSumInput.hide();
                            $linkedSumFakeHolder.show();
                        }
                    }

                    function initCheckboxInput() {
                        $checkBoxInput.change(function () {
                            if ($(this).prop('checked')) {
                                $linkedSumInput.val(getAvailableLinkSum().toFixed(2)).change();

                                $linkedSumInput.show();
                                $linkedSumFakeHolder.hide();
                            } else {
                                $linkedSumInput.hide();
                                $linkedSumFakeHolder.show();

                                $linkedSumInput.val(0).change();
                            }
                        });
                    }

                    function getInputLinkedSumValue() {
                        var value = parseFloat($linkedSumInput.val());

                        if (!value)
                            return 0;

                        return value;
                    }

                    function getAvailableLinkSum() {
                        var paymentSumLeft = self.getPaymentSumLeft() + getInputLinkedSumValue(),
                            invoiceSumLeft = getInvoiceSumLeft(true);

                        if (paymentSumLeft >= invoiceSumLeft) {
                            return invoiceSumLeft;
                        } else {
                            return paymentSumLeft;
                        }
                    }
                });
            };

            this.getPaymentSumLeft = function () {
                var output = paymentSum;

                $invoicesHolder.find('.item').each(function (key, el) {
                    var $item = $(el),
                        $checkBoxInput = $item.find('.js-checked'),
                        $linkedSumInput = $item.find('.js-input-sum');

                    if ($checkBoxInput.prop('checked')) {
                        var value = parseFloat($linkedSumInput.val());

                        if (value) {
                            output -= value;
                        }
                    }
                });

                return output;
            };

            this.showPaymentSumLeft = function () {
                var value = self.getPaymentSumLeft();

                if (value > 0) {
                    $sumLeftHolder.html("<div class='text-warning'>Осталось связать: " + $.format.number(value, '#,##0.00#') + "</div>");
                } else if (value == 0) {
                    $sumLeftHolder.html("<div class='text-success'>Связана вся сумма</div>")
                } else {
                    $sumLeftHolder.html("<div class='text-danger'>Переизбыток: " + $.format.number(value, '#,##0.00#') + "</div>");
                }
            };

            this.validateLinkedInvoice = function (invoiceId, linkedSum) {
                invoiceId = parseInt(invoiceId);
                linkedSum = parseFloat(linkedSum);

                var output = {state: true, errors: []};

                // Ищем необходимый счет в списке
                var invoice = false;
                $.each(invoiceList, function (index, item) {
                    var itemId = parseInt(item.id);

                    if (itemId == invoiceId) {
                        invoice = item;
                    }
                });

                if (invoice === false) {
                    output.state = false;
                    output.errors.push('Неверный ID счета: ' + invoiceId);

                    return output;
                }

                function getInvoiceSumLeft() {
                    var invoiceSummary = parseFloat(invoice.summary),
                        invoiceTotalPaid = parseFloat(invoice.total_paid);

                    // Корректируем invoiceTotalPaid, если в этой сумме фигурирует линковка из данного поступления
                    if (invoice.linked) {
                        invoiceTotalPaid -= parseFloat(invoice.linked_sum);
                    }

                    return invoiceSummary - invoiceTotalPaid;
                }

                if (linkedSum > getInvoiceSumLeft()) {
                    output.state = false;
                    output.errors.push('Указана слишком большая сумма привязки для счета: ' + invoice.name);
                }

                return output;
            };

            this.submitForm = function () {
                var postData = {
                    paymentId: payment.id,
                    linkedInvoices: {}
                };
                // Сначала валидируем значения формы
                var errors = [];

                if (self.getPaymentSumLeft() < 0) {
                    errors.push('Общая сумма связки со счетами превышает объем средств поступления.');
                }

                $invoicesHolder.find('.item').each(function (index, el) {
                    var $item = $(el),
                        itemId = $item.data('id'),
                        itemValue = $item.find('.js-input-sum').val(),
                        $checkBoxInput = $item.find('.js-checked');

                    if (!$checkBoxInput.prop('checked'))
                        return true;

                    postData.linkedInvoices[itemId] = itemValue;

                    var validateResult = self.validateLinkedInvoice(itemId, itemValue);

                    if (!validateResult.state) {
                        $.each(validateResult.errors, function (index, error) {
                            errors.push(error);
                        });
                    }
                });

                if (errors.length > 0) {
                    alert(errors.join("\n"));
                    return false;
                }

                // Если ошибок нет, то отправляем данные на сервер
                $.post($form.attr('action'), postData, function (response) {
                    if (response.state) {
                        // Все прошло успешно, проверяем, необходимо ли обновить какой либо pjax контейнер и закрываем модальное окно
                        var updatePjax = $form.data('update-pjax');
                        if (updatePjax && $(updatePjax).length > 0) {
                            $.pjax({
                                url: window.location.href,
                                container: $(updatePjax),
                                scrollTo: false,
                                timeout: 8000
                            });
                        }

                        $modal.modal('hide');
                    } else {
                        alert(response.errors.join("\n"));
                    }
                }).error(function () {
                    alert('Произошла ошибка при отправке запроса на сервер.');
                });
            };

            this._init = function () {
                // Очищаем ранее сгенерированные счета
                $invoicesHolder.html('');
                // Очищаем старое описание платежа
                $paymentDescriptionHolder.html('');
                // Убираем старые обработчики submit формы
                $form.unbind('beforeSubmit');

                // Отображаем описание платежа
                $paymentDescriptionHolder.html(payment.description);
                // Генерируем счета для связки
                self.printInvoices();
                // Отображаем оставшуюся сумму для связывания
                self.showPaymentSumLeft();
                // Привязываем обработчик submit формы
                $form.on('beforeSubmit', function (e) {
                    self.submitForm();

                    return false;
                });
            };

            self._init();
        }

        // File Upload Button
        $('.file-upload button').click(function (e) {
            e.preventDefault();

            var $fileUpload = $(this).parents('.file-upload'),
                uploadConfirm = $fileUpload.data('upload-confirm');

            if (uploadConfirm) {
                if (confirm(uploadConfirm)) {
                    $fileUpload.find('input[type=file]').click();
                }
            } else {
                $fileUpload.find('input[type=file]').click();
            }
        });

        $(document).on('change', '.file-upload input[type=file]', function (e) {
            var $input = $(this),
                $fileUpload = $input.parents('.file-upload'),
                uploadUrl = $fileUpload.data('upload-url'),
                callbackName = $fileUpload.data('callback-name');

            if (uploadUrl) {
                var formData = new FormData();
                formData.append($input.attr('name'), $input[0].files[0]);

                $.ajax({
                    url: uploadUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (callbackName && window[callbackName]) {
                            window[callbackName](response);
                        }
                    },
                    error: function () {
                        alert('Произошла ошибка при выполнении запроса.');
                    },
                    complete: function () {
                        var $newInput = $("<input type='file'>").attr('name', $input.attr('name'));

                        $input.replaceWith($newInput);
                    }
                });
            }
        });
    });
})(jQuery);