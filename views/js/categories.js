/*
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *	@author    PrestaShop SA <contact@prestashop.com>
 *	@copyright	2007-2016 PrestaShop SA
 *	@license   http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
 *	International Registered Trademark & Property of PrestaShop SA
 */

function loadCategoryMatch(id_category) {
    $.ajax({
        async: false,
        type: "POST",
        url: module_dir + 'ebay/ajax/loadCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&ch_cat_str=' + categories_ebay_l['no category selected'] + "&profile=" + id_ebay_profile,
        success: function (data) {
            $("#categoryPath" + id_category).html();
        }
    });
}

function changeCategoryMatch(level, id_category) {
    var levelParams = "&level1=" + $("#categoryLevel1-" + id_category).val();

    if (level > 1) levelParams += "&level2=" + $("#categoryLevel2-" + id_category).val();
    if (level > 2) levelParams += "&level3=" + $("#categoryLevel3-" + id_category).val();
    if (level > 3) levelParams += "&level4=" + $("#categoryLevel4-" + id_category).val();
    if (level > 4) levelParams += "&level5=" + $("#categoryLevel5-" + id_category).val();

    $.ajax({
        type: "POST",
        url: module_dir + 'ebay/ajax/changeCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&level=' + level + levelParams + '&ch_cat_str=' + categories_ebay_l['no category selected'] + '&profile=' + id_ebay_profile,
        success: function (data) {
            $("#categoryPath" + id_category).html(data);
        }
    });
}

var loadedCategories = new Array();

function showProducts(id_category) {
    var elem = $('#show-products-switch-' + id_category);
    var elem_string = $('#show-products-switch-string' + id_category);

    if (elem.attr('showing') == true) {
        $('.product-table-row[category=' + id_category + ']').hide();
        elem.attr('showing', 0);
        elem.html('&#9654;');
//		elem_string.html(categories_ebay_l['Unselect products']);
    }
    else {
        elem.attr('showing', 1);
        elem.html('&#9660;');
//		elem_string.html(categories_ebay_l['Unselect products clicked']);

        if (loadedCategories[id_category])
            $('.product-table-row[category=' + id_category + ']').show();
        else {
            $('<img src="' + module_path + 'views/img/loading-small.gif" id="loading-' + id_category + '" alt="" />').insertAfter(elem);

            $.ajax({
                dataType: 'json',
                type: "POST",
                url: module_dir + 'ebay/ajax/getProducts.php?category=' + id_category + '&token=' + ebay_token + '&id_ebay_profile=' + id_ebay_profile,
                success: function (products) {
                    loadedCategories[id_category] = true;

                    var str = '<tr class="product-table-row" category="' + id_category + '"><td colspan="7"><table class="table tableDnD" width="80%" style="margin: auto">';

                    str += '<tr class="product-row" category="' + id_category + '"> \
            <th class="">' + categories_ebay_l['Products'] + '</th> \
            <th class="ebay_center ">' + categories_ebay_l['Stock'] + '</th> \
            <th class="ebay_center ">' + categories_ebay_l['Unselect products'] + '</th> \
          </tr>';

                    for (var i in products) {
                        var product = products[i];

                        str += '<tr class="product-row ' + (i % 2 == 0 ? 'alt_row' : '') + '" category="' + id_category + '"> \
							<td>' + product.name + '</td> \
							<td class="ebay_center">' + (parseInt(product.stock) ? product.stock : '<span class="red">0</span>') + '</td> \
							<td class="ebay_center"> \
								<input name="showed_products[' + product.id + ']" type="hidden" value="1" /> \
								<input onchange="toggleSyncProduct(' + id_category + ')" class="sync-product" category="' + id_category + '" name="to_synchronize[' + product.id + ']" type="checkbox" ' + (product.blacklisted == 1 ? '' : 'checked') + ' /> \
							</td> \
						</tr>';
                    }

                    str += '</table></td></tr>';

                    $('#category-' + id_category).after(str);
                    $('#loading-' + id_category).remove();
                }
            });
        }
    }
}

function toggleSyncProduct(category_id) {

    var nbSelected = 0;

    var hasNotSelected = false;

    $('.sync-product[category=' + category_id + ']').each(function () {

        if ($(this).attr('checked'))
            nbSelected++;
        else if (!hasNotSelected)
            hasNotSelected = true;

    });

    var str = '';
    if (hasNotSelected)
        str = '<span class="bold">' + nbSelected + '</span>';
    else
        str = nbSelected;

    $('.cat-nb-products[category=' + category_id + ']').html(str);
}

function initCategoriesPagination() {

    $("#pagination").children('li').click(function () {

        var p = $(this).html();

        var li = $("#pagination").children('li.current');

        if ($(this).attr('class') == 'prev') {

            var liprev = li.prev();
            if (!liprev.hasClass('prev')) {
                liprev.trigger('click');
            }
            return false;

        }
        if ($(this).attr('class') == 'next') {

            var linext = li.next();
            if (!linext.hasClass('next')) {
                linext.trigger('click');
            }
            return false;

        }

        $("#pagination").children('li').removeClass('current');
        $(this).addClass('current');

        $("#textPagination").children('span').html(p);
        $.ajax({
            type: "POST",
            dataType: "json",
            url: module_dir + "ebay/ajax/saveCategories.php?token=" + ebay_token + "&profile=" + id_ebay_profile,
            data: $('#configForm2').serialize() + "&ajax=true",
            success: function (data) {
                if (data.valid) {
                    loadCategories(p, $('#cat-filter').val());
                }
            }
        });
    })

}

function loadCategories(page, search) {

    var url = module_dir + "ebay/ajax/loadTableCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile + '&ch_cat_str=' + categories_ebay_l['no category selected'] + '&ch_no_cat_str=' + categories_ebay_l['no category found'] + '&not_logged_str=' + categories_ebay_l['You are not logged in'] + '&unselect_product=' + categories_ebay_l['Unselect products'] + '&id_shop=' + id_shop + '&admin_path=' + admin_path;

    if (page != undefined)
        url += "&p=" + page;
    if (search != undefined)
        url += "&s=" + search;

    $.ajax({
        type: "POST",
        url: url,
        success: function (data) {

            $("form#configForm2 table tbody #removeRow").remove();
            $("form#configForm2 table tbody").html(data);

            $('#cat-pagination-holder').html($('#cat-pagination'));
            $('#cat-pagination').show();

            initCategoriesPagination();

        }
    });

}

$(document).ready(function () {

    $('#cat-filter').keyup(function () {
        loadCategories(0, $(this).val());
    });

    loadCategories();

    $("#configForm2SuggestedCategories input[type=submit]").click(function () {
        $('<div class="ebay_center"><img src="' + module_path + 'views/img/loading-small.gif" alt="" />' + categories_ebay_l['thank you for waiting'] + '</div>').insertAfter($(this));
        $(this).fadeOut();
        $.ajax({
            type: "POST",
            url: module_dir + "ebay/ajax/suggestCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile + '&not_logged_str=' + categories_ebay_l['You are not logged in'] + '&settings_updated_str=' + categories_ebay_l['Settings updated'],
            success: function (data) {
                window.location.href = window.location.href + "&conf=6";
            }
        });
        return false;
    });

    $('#update-all-extra-images').click(function () {
        var val = $('#all-extra-images-selection').val();
        $('#all-extra-images-value').val(val);
    });

    if ($("#menuTab1").hasClass('success') && $("#menuTab2").hasClass('wrong') && $("#configForm2SuggestedCategories input[type=submit]").length == 1) {
        //$("#configForm2SuggestedCategories input[type=submit]").trigger("click");
    }
});


// Import Category From eBay
function loadCategoriesFromEbay(step, id_category, row) {
    alertOnExit(true, alert_exit_import_categories);
    step = typeof step !== 'undefined' ? step : 1;
    id_category = typeof id_category !== 'undefined' ? id_category : false;
    row = typeof row !== 'undefined' ? row : 2;

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: module_dir + 'ebay/ajax/loadCategoriesFromEbay.php?token=' + ebay_token + "&profile=" + id_ebay_profile + "&step=" + step + "&id_category=" + id_category + "&admin_path=" + admin_path,
        success: function (data) {
            if (data == "error") {
                if (step == 1) {
                    $('#cat_parent').addClass('error');
                    $('#cat_parent td:nth-child(3)').text(categories_ebay_l['An error has occurred']);
                }
                else if (step == 2) {
                    $('#load_cat_ebay tbody tr:nth-child(' + row + ')').addClass('error');
                }
                alertOnExit(false, "");
            }
            else {
                var output;

                if (step == 1) {
                    for (var i in data) {
                        output += '<tr class="standby" data-id="' + data[i].CategoryID + '"><td></td><td>' + categories_ebay_l['Download subcategories of'] + ' ' + data[i].CategoryName + '</td><td>' + categories_ebay_l['Waiting'] + '</td></tr>';
                    }
                    var count = $.map(data, function (n, i) {
                        return i;
                    }).length;
                    $('#cat_parent').removeClass('load').addClass('success');
                    $('#cat_parent td:nth-child(3)').text(categories_ebay_l['Finish'] + ' - ' + count + ' ' + categories_ebay_l['categories loaded success']);
                    $('#load_cat_ebay tbody').append(output);

                    $('#load_cat_ebay tbody tr:nth-child(2)').addClass('load');
                    loadCategoriesFromEbay(2, $('#load_cat_ebay tbody tr:nth-child(2)').attr('data-id'), 2);
                }
                else if (step == 2) {
                    var count = $.map(data, function (n, i) {
                        return i;
                    }).length;
                    $('#load_cat_ebay tbody tr:nth-child(' + row + ')').removeClass('load').addClass('success');

                    $('#load_cat_ebay tbody tr:nth-child(' + row + ') td:nth-child(3)').text(categories_ebay_l['Finish'] + ' - ' + count + ' ' + categories_ebay_l['categories loaded success']);

                    var next = row + 1;
                    if ($('#load_cat_ebay tbody tr:nth-child(' + next + ')').length > 0) {
                        $('#load_cat_ebay tbody tr:nth-child(' + next + ')').addClass('load');
                        loadCategoriesFromEbay(2, $('#load_cat_ebay tbody tr:nth-child(' + next + ')').attr('data-id'), next);
                    }
                    else {
                        loadCategoriesFromEbay(3);
                        $('#load_cat_ebay').css('display', 'none');
                        $('.hidden.importCatEbay').removeClass('hidden').removeClass('importCatEbay');
                        $('.warning.big.tips.h').show();
                        alertOnExit(false, "");
                        $('#menuTab2').removeClass('succes');
                        $('#menuTab2').addClass('wrong');
                        $('#menuTab8').removeClass('succes');
                        $('#menuTab8').addClass('wrong');
                        
                        return loadCategories();

                    }
                    alertOnExit(false, "");
                }

            }
        }
    });

}
