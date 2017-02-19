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

        $(document).on('keyup', '.js-input-summary', function (e) {
            var $item = $(this).parents('.item'),
                $quantity = $item.find('.js-input-quantity'),
                $price = $item.find('.js-input-price'),
                $summary = $item.find('.js-input-summary');

            var q = parseFloat($quantity.val()),
                p = parseFloat($price.val()),
                s = parseFloat($summary.val());

            if (q && p && s) {
                $price.val((s / q).toFixed(2));
            }

            calculateInvoiceSummary();
        });

        // Скрипт автоматического расчета суммы позиции счета при изменении кол-ва или стоимости за единицу
        $(document).on('keyup', '.js-input-quantity, .js-input-price', function (e) {
            var $item = $(this).parents('.item'),
                $quantity = $item.find('.js-input-quantity'),
                $price = $item.find('.js-input-price'),
                $summary = $item.find('.js-input-summary');

            var q = parseFloat($quantity.val()),
                p = parseFloat($price.val());

            if (q && p) {
                $summary.val((q * p).toFixed(2));

                calculateInvoiceSummary();
            }
        });
    });
})(jQuery);