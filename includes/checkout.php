
<div class="payon_ins_wrap">
    <div class="payon_ins_box">
        <div class="payon_ins_prod">
            <div class="payon_ins_prod_left">
                <?php echo wp_kses_post($product->get_image()); ?>
            </div>
            <div class="payon_ins_prod_right">
                <h1>Mua trả góp:
                    <a href="<?php echo esc_html_e($product->get_permalink()); ?>" title="<?php echo esc_html_e($product->get_name()); ?>">
                        <?php echo esc_html_e($product->get_name()); ?>
                    </a>
                </h1>
                <span>Giá bán:
                    <strong>
                        <span class="amount">
                            <bdi>
                                <span ></span>
                                <?php echo esc_html_e(number_format($product->get_price(), 0, ",", ".")); ?>
                            </bdi>
                        </span>
                        ₫
                    </strong>
                </span>
            </div>
        </div>
        <div class="">
            <?php if(get_option('show_prepay') == 'yes'): ?>
            <div class="payon_box">
                <div class="payon_col">
                    <label for="payon_prepaid" class="payon_title">Bước 1: Số tiền trả trước</label>
                    <div class="list_radio_style">
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_0" value="0" checked="checked">
                            <span><strong>Trả góp toàn bộ</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_all, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php if($pri_10 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_10" value="10">
                            <span><strong>Trả trước 10%</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_10, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                        <?php if($pri_20 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_20" value="20">
                            <span><strong>Trả trước 20%</strong><span class="amount"><bdi><?php echo esc_html_e(number_format($pri_20, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                        <?php if($pri_30 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_30" value="30">
                            <span><strong>Trả trước 30%</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_30, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                        <?php if($pri_40 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_40" value="40">
                            <span><strong>Trả trước 40%</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_40, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                        <?php if($pri_50 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_50" value="50">
                            <span><strong>Trả trước 50%</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_50, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                        <?php if($pri_60 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_60" value="60">
                            <span><strong>Trả trước 60%</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_60, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                        <?php if($pri_70 >= $installment_amount): ?>
                        <label onclick="clickPrepaid()">
                            <input type="radio" name="payon_prepaid" class="payon_prepaid_70" value="70">
                            <span><strong>Trả trước 70%</strong>
                            <span class="amount"><bdi><?php echo esc_html_e(number_format($pri_70, 0, ",", ".")); ?><span ><?php echo esc_html_e($dv); ?></span></bdi></span></span>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="payon_box">
                <div class="payon_label payon_ins_title"><?php echo (get_option('show_prepay') == 'yes') ? 'Bước 2:' : 'Bước 1:'; ?> Chọn ngân hàng trả góp</div>
                <div class="payon_listbank_mess"></div>
                <div class="payon_listbank">
                    <?php foreach ($listBanks as $bank) : ?>
                        <label data-code='<?php esc_html_e($bank['code']); ?>' data-card='<?php esc_html_e(json_encode($bank["installment_card_type"])); ?>' title='<?php esc_html_e($bank['full_name']); ?>'>
                            <input value='<?php esc_html_e($bank['code']); ?>' name='payon_bank' type='radio'>
                            <span id='card-<?php esc_html_e($bank['code']); ?>' class='bg-white' onclick='devClick("<?php esc_html_e($bank["code"]); ?>");' data-card='<?php esc_html_e(json_encode($bank['installment_card_type'])) ?>' data-name='<?php esc_html_e($bank["full_name"]); ?>' data-cycle='<?php esc_html_e(json_encode($bank['cycle'])); ?>'><img data-lazyloaded='1' src='<?php esc_html_e($bank['logo']); ?>' data-src='<?php esc_html_e($bank['logo']); ?>' alt='<?php esc_html_e($bank['full_name']); ?>' data-ll-status='loaded' class='entered litespeed-loaded'></span>
                            <input type="hidden" id="rule-bank-<?php esc_html_e($bank['code']); ?>" value="<?php esc_html_e($bank['installment_rule']); ?>">
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="mess-bank" style="color: #D57B00;"></div>
            <div class="payon_box payon_card_box">
                <div class="payon_label payon_ins_title"><?php echo (get_option('show_prepay') == 'yes') ? 'Bước 3:' : 'Bước 2:'; ?> Chọn loại thẻ</div>
                <div class="payon-listbank">
                    <ul class="list">
                        <li>
                            <label class="bg-white" data-code="visa" title="visa">
                                <input value="visa" name="payon_card" type="radio">
                                <span onclick="checkData('visa')"><img src="<?php echo esc_html_e(plugins_url('assets/images/visa.png', plugin_dir_path(__FILE__))); ?>" alt="visa"></span>
                            </label>
                        </li>
                        <li>
                            <label class="bg-white" data-code="mastercard" title="mastercard">
                                <input value="mastercard" name="payon_card" type="radio">
                                <span onclick="checkData('mastercard')"><img src="<?php echo esc_html_e(plugins_url('assets/images/master.png', plugin_dir_path(__FILE__))); ?>" alt="mastercard"></span>
                            </label>
                        </li>
                        <li>
                            <label class="bg-white" data-code="jcb" title="jcb">
                                <input value="jcb" name="payon_card" type="radio">
                                <span onclick="checkData('jcb')"><img src="<?php echo esc_html_e(plugins_url('assets/images/jcb.png', plugin_dir_path(__FILE__))); ?>" alt="jcb"></span>
                            </label>
                        </li>
                    </ul>
                </div>
                <p id="loading">Đang tải dữ liệu...</p>
            </div>

            <div class="payon_ins_box payon_cycle_box" style="display: none;">
                <div class="payon_ins_col">
                    <label for="payon_prepaid" class="payon_ins_title"><?php echo (get_option('show_prepay') == 'yes') ? 'Bước 4:' : 'Bước 3:'; ?> chọn kỳ trả góp</label>
                    <span class="radio_mess"></span>
                    <div class="list_radio_style payon_cycle_wrap">
                        <label class="payon_prepaid_cycle" onclick="checkDataIns(3)" data-month-cycle="3">
                            <input type="radio" name="payon_cycle" id="payon_cycle_3" class="payon_cycle_3" value="3">
                            <span><strong>3</strong> Tháng</span>
                        </label>
                        <label class="payon_prepaid_cycle" data-month-cycle="6" onclick="checkDataIns(6)">
                            <input type="radio" name="payon_cycle" id="payon_cycle_6" class="payon_cycle_6" value="6">
                            <span><strong>6</strong> Tháng</span>
                        </label>
                        <label class="payon_prepaid_cycle" data-month-cycle="9" onclick="checkDataIns(9)">
                            <input type="radio" name="payon_cycle" id="payon_cycle_9" class="payon_cycle_9" value="9">
                            <span><strong>9</strong> Tháng</span>
                        </label>
                        <label class="payon_prepaid_cycle" data-month-cycle="12" onclick="checkDataIns(12)">
                            <input type="radio" name="payon_cycle" id="payon_cycle_12" class="payon_cycle_12" value="12">
                            <span><strong>12</strong> Tháng</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="payon_ins_box total_payon_wrap" style="display: none;">
                <div class="fee-desktop">
                    <h3 style="text-align: right;">Số tiền cần thanh toán khi nhận hàng: <span id="total_payed"></span></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Gói trả góp</th>
                                <th class="payon_month" data-month-true="3"><span>3 tháng</span></th>
                                <th class="payon_month" data-month-true="6"><span>6 tháng</span></th>
                                <th class="payon_month" data-month-true="9"><span>9 tháng</span></th>
                                <th class="payon_month" data-month-true="12"><span>12 tháng</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Trả góp mỗi tháng</td>
                                <td class="payon_month" data-month-true="3">
                                    <span class="red total_month_3"></span>
                                </td>
                                <td class="payon_month" data-month-true="6">
                                    <span class="red total_month_6"></span>
                                </td>
                                <td class="payon_month" data-month-true="9">
                                    <span class="red total_month_9"></span>
                                </td>
                                <td class="payon_month" data-month-true="12">
                                    <span class="red total_month_12"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Tổng tiền trả góp tạm tính</td>
                                <td class="payon_month" data-month-true="3">
                                    <span class="total_payon_3"></span>
                                </td>
                                <td class="payon_month" data-month-true="6">
                                    <span class="total_payon_6"></span>
                                </td>
                                <td class="payon_month" data-month-true="9">
                                    <span class="total_payon_9"></span>
                                </td>
                                <td class="payon_month" data-month-true="12">
                                    <span class="total_payon_12"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Chênh lệch so với trả thẳng</td>
                                <td class="payon_month" data-month-true="3">
                                    <span class="chenhlech_payon_3"></span>
                                    <button type="button" class="btn btn-default btn-choose-cycle mr-0" data-month="3" onclick="checkDataCyclev1(3)">Chọn 3 tháng</button>
                                </td>
                                <td class="payon_month" data-month-true="6">
                                    <span class="chenhlech_payon_6"></span>
                                    <button type="button" class="btn btn-default btn-choose-cycle mr-0" data-month="6" onclick="checkDataCyclev1(6)">Chọn 6 tháng</button>
                                </td>
                                <td class="payon_month" data-month-true="9">
                                    <span class="chenhlech_payon_9"></span>
                                    <button type="button" class="btn btn-default btn-choose-cycle mr-0" data-month="9" onclick="checkDataCyclev1(9)">Chọn 9 tháng</button>
                                </td>
                                <td class="payon_month" data-month-true="12">
                                    <span class="chenhlech_payon_12"></span>
                                    <button type="button" class="btn btn-default btn-choose-cycle mr-0" data-month="12" onclick="checkDataCyclev1(12)">Chọn 12 tháng</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <input type="hidden" id="fee-by-month" name="fee-by-month" value="">
            <input type="hidden" id="total-installment" name="total-installment" value="">
            <input type="hidden" id="money-different" name="money-different" value="">
            <div class="payon_infor_customer">
                <div class="payon_ins_box">
                    <label class="payon_ins_title">Thông tin người mua</label>
                    <div class="payon_ins_col1">
                        <input type="text" name="payon_name" onkeyup="valid()" id="payon_name" placeholder="Họ và tên" value="" required="" class="valid" aria-invalid="false" required>
                    </div>
                    <div class="payon_ins_col2">
                        <input type="text" name="payon_phone" onkeyup="valid()" id="payon_phone" placeholder="Số điện thoại" value="" required="" class="valid" aria-invalid="false">
                        <span id="valid-phone"></span>
                    </div>
                </div>
                <div class="payon_ins_box">
                    <div class="payon_ins_col1">
                        <input type="text" name="payon_email" id="payon_email" onkeyup="valid()" placeholder="Email của bạn" value="" class="valid" aria-invalid="false">
                        <span id="valid-email"></span>
                    </div>
                    <div class="payon_ins_col2">
                        <input type="text" name="payon_address" onkeyup="valid()" id="payon_address" placeholder="Địa chỉ của bạn" value="" class="valid" aria-invalid="false" required>
                    </div>
                </div>
                <div class="payon_ins_box">
                    <input type="hidden" id="payon_nonce" name="payon_nonce" value="42bf4816c7">
                    <!-- <input type="hidden" name="_wp_http_referer" value="/tra-gop-payon/13-san-pham-1/"> <input type="hidden" value="13" name="prod_id"> -->
                    <button type="submit" id="create_order" class="button alt" onclick="createOrder();" disabled data-value="Thanh toán">Thanh toán</button> 
                    <p class="text-center" id="loading-2" style="display: none;">Đang thực hiện thanh toán...</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    window.scrollTo({ top: 0, behavior: 'smooth' });
    var payon_bank;
    var payon_card;
    var payon_cycle;
    var cycle_selected;
    var data_name;
    var cycled = 0;
    const validateEmail = (email) => {
        return email.match(
            /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        );
    };


    const validatePhone = (phone) => {
        return phone.match(/\d/g);
    };

    const validate = () => {
        const $result = jQuery('#valid-email');
        const $result_phone = jQuery('#valid-phone');
        const email = jQuery('#payon_email').val();
        const name = jQuery('#payon_name').val();
        const address = jQuery('#payon_address').val();
        const phone = jQuery('#payon_phone').val();
        $result.text('');
        $result_phone.text('');
        var valid = false;

        if (validateEmail(email)) {
            $result.text('');
            $result.hide();
        } else {
            if (email.length > 0) {
                $result.text(email + ' không đúng định dạng');
                $result.css('color', 'red');
            }
        }

        if (validatePhone(phone) && phone.length === 10) {
            $result_phone.text('');
            $result_phone.hide();
        } else {
            if (phone.length > 0) {
                $result_phone.text(phone + ' không đúng định dạng');
                $result_phone.css('color', 'red');
            }
        }
        if (name.length > 1 && address.length > 5 && validatePhone(phone) && validateEmail(email) && payon_bank && payon_card && cycled > 0) {
            document.getElementById("create_order").disabled = false;
        } else {
            document.getElementById("create_order").disabled = true;
        }
    }

    function valid() {
        jQuery('#payon_email').on('input', validate);
        jQuery('#payon_name').on('input', validate);
        jQuery('#payon_address').on('input', validate);
        jQuery('#payon_phone').on('input', validate);
    }

    function devClick(data) {
        jQuery("#loading").hide();
        jQuery(".payon_cycle_box").css("display", "none");
        var myDiv = document.querySelector("#card-" + data);
        var data_card = myDiv.dataset.card;
        payon_cycle = myDiv.dataset.cycle;
        payon_bank = data;
        jQuery(".total_payon_wrap").css("display", "none");
        var mess_bank = jQuery('#rule-bank-' + data).val();
        jQuery("#mess-bank").html();
        if (mess_bank) {
            jQuery("#mess-bank").html(mess_bank);
            jQuery("#mess-bank").css("display", "block");
        }
        if (data_card) {
            data_card = JSON.parse(data_card);
            jQuery(".payon_card_box").css("display", "block");
            jQuery(".devvn_card_click").each(function() {
                var data_card_box = jQuery(this).attr("data-code");
                if (jQuery.inArray(data_card_box, data_card) !== -1) {
                    jQuery(this).removeAttr("hidden");
                } else {
                    jQuery(this).prop("hidden", true);
                }
            });
            jQuery("input[name=payon_card]").prop("checked", false);
        }

    }

    var radioValue = 0;
    function checkData(card) {
        jQuery(".total_payon_wrap").css("display", "none");
        jQuery(".payon_cycle_box").css("display", "none");
        payon_card = card;
        var load_payon_cycle = JSON.parse(payon_cycle);
        for (var i = 0; i < load_payon_cycle.length; i++) {

            let collection = document.getElementsByClassName("month_" + load_payon_cycle[i]);
            for (let i = 0; i < collection.length; i++) {
                collection[i].style.display = "none";
                collection[i].text = "";
            }
        }
        if (payon_bank && payon_card && payon_cycle) {
            var payon_cycle_data = JSON.parse(payon_cycle);
            payon_cycle_data = JSON.stringify(payon_cycle_data);
            jQuery("#loading").show();
            radioValue = jQuery("input[name='payon_prepaid']:checked").val();
            if(!radioValue){
                radioValue = 0;
            }
            var amount_product = parseInt(<?php echo $product->get_price() ?>);
            var amount = amount_product - amount_product*radioValue/100;
            var url = "<?php echo get_rest_url(null, "/payon/fee") ?>";
            jQuery.ajax({
                url: url,
                type: "POST",
                data: {
                    payon_bank: payon_bank,
                    payon_card: payon_card,
                    payon_cycle: payon_cycle_data,
                    amount: amount,
                    async: false,
                },
                dataType: "JSON",
                success: function(response) {
                    jQuery("#loading").css("display", "none");
                    let total_payed = new Intl.NumberFormat('de-DE').format(parseInt(amount_product*radioValue/100));
                    jQuery("#total_payed").html(total_payed+response.dv);
                    var load_payon_cycle = JSON.parse(payon_cycle);
                    for (var i = 0; i < load_payon_cycle.length; i++) {
                        if (load_payon_cycle[i] == 3) {
                            jQuery(".chenhlech_payon_3").html(response.different.m3);
                            jQuery(".total_payon_3").html(response.totalInstallment.m3);
                            jQuery(".total_month_3").html(response.feeByMonth.m3);
                        }
                        if (load_payon_cycle[i] == 6) {
                            jQuery(".chenhlech_payon_6").html(response.different.m6);
                            jQuery(".total_payon_6").html(response.totalInstallment.m6);
                            jQuery(".total_month_6").html(response.feeByMonth.m6);
                        }
                        if (load_payon_cycle[i] == 9) {
                            jQuery(".chenhlech_payon_9").html(response.different.m9);
                            jQuery(".total_payon_9").html(response.totalInstallment.m3);
                            jQuery(".total_month_9").html(response.feeByMonth.m9);
                        }
                        if (load_payon_cycle[i] == 12) {
                            jQuery(".chenhlech_payon_12").html(response.different.m12);
                            jQuery(".total_payon_12").html(response.totalInstallment.m12);
                            jQuery(".total_month_12").html(response.feeByMonth.m12);
                        }
                    }
                    jQuery("#payon_cycle").val(payon_cycle);
                    jQuery("#cycle").val(payon_cycle);
                    if (cycled > 0) {
                        checkDataIns(cycled);
                        checkDataCyclev1(cycled);
                        document.getElementById("payon_cycle_"+cycled).checked = true;
                    }
                    checkDataCycle(payon_bank);
                },
            });
        }
    }

    function checkDataCycle(data) {
        if(screen.width > 750){
            jQuery(".total_payon_wrap").css("display", "block");
            var myDiv = document.querySelector("#card-" + data);
            var data_cycle = myDiv.dataset.cycle;
            data_cycle = JSON.parse(data_cycle);
            jQuery(".payon_month").each(function() {
                var data_month_true = jQuery(this).attr("data-month-true");
                if (jQuery.inArray(parseInt(data_month_true), data_cycle) !== -1) {
                    jQuery(this).removeAttr("hidden");
                } else {
                    jQuery(this).prop("hidden", true);
                }
            });
            jQuery(".btn-choose-cycle").css("display", "block");
        } else {
            jQuery(".payon_cycle_box").css("display", "block");
            jQuery(".btn-choose-cycle").css("display", "none");
        }
        
    }

    function checkDataCyclev1(data) {
        jQuery(".btn-choose-cycle").each(function() {
            var data_month_cycle = jQuery(this).attr("data-month");
            if (parseInt(data_month_cycle) == data) {
                ;
                jQuery(this).addClass("active-button");
            } else {
                jQuery(this).removeClass("active-button")
            }
        });
        cycled = data;
        let feemonth = jQuery(".total_month_" + data).html();
        let total_installment = jQuery(".total_payon_" + data).html();
        let different = jQuery(".chenhlech_payon_" + data).html();
        jQuery("#fee-by-month").val(feemonth);
        jQuery("#total-installment").val(total_installment);
        jQuery("#money-different").val(different);
    }

    function checkDataIns(data) {
        jQuery(".total_payon_wrap").css("display", "block");
        // jQuery("#loading").show();
        var myDiv = document.querySelector("#card-" + payon_bank);
        data_name = myDiv.dataset.name;
        let feemonth = jQuery(".total_month_" + data).html();
        let total_installment = jQuery(".total_payon_" + data).html();
        let different = jQuery(".chenhlech_payon_" + data).html();
        jQuery("#fee-by-month").val(feemonth);
        jQuery("#total-installment").val(total_installment);
        jQuery("#money-different").val(different);
        var load_payon_cycle = JSON.parse(payon_cycle);
        
        jQuery(".payon_month").each(function() {
            var data_month_true = jQuery(this).attr("data-month-true");
            jQuery(this).prop("hidden", true);
            if (parseInt(data_month_true) == parseInt(data)) {
                jQuery(this).removeAttr("hidden");
            }
        });
        cycled = data;

    }

    function clickPrepaid()
    {
        let radioValueNew = jQuery("input[name='payon_prepaid']:checked").val();
        if(payon_bank && payon_card && payon_cycle && radioValue != radioValueNew){
            radioValue = radioValueNew;
            checkData(payon_card);
        }
    }

    function createOrder() {
        document.getElementById("create_order").disabled = true;
        jQuery('#loading-2').show();
        var radioValue = jQuery("input[name='payon_prepaid']:checked").val();
        var amount = parseInt(<?php echo $product->get_price() ?>);
        amount = amount - amount*radioValue/100;
        var fullname = jQuery("#payon_name").val();
        var address = jQuery("#payon_address").val();
        var email = jQuery("#payon_email").val();
        var phone = jQuery("#payon_phone").val();
        var product_id = parseInt(<?php echo $product->get_id() ?>);
        var feemonth = jQuery("#fee-by-month").val();
        var total_installment = jQuery("#total-installment").val();
        var different = jQuery("#money-different").val();
        var url = "<?php echo get_rest_url(null, "/payon/create-order") ?>";
        jQuery.ajax({
            url: url,
            type: "POST",
            data: {
                payon_bank: payon_bank,
                payon_card: payon_card,
                payon_cycle: cycled,
                amount: amount,
                pripaid: radioValue,
                phone: phone,
                email: email,
                fullname: fullname,
                address: address,
                product_id: product_id,
                fee_by_month: feemonth,
                total_installment: total_installment,
                money_different: different,
                async: false,
            },
            dataType: "JSON",
            success: function(response) {
                document.getElementById("create_order").disabled = false;
                jQuery('#loading-2').hide();
                if (response.code == 200) {
                    window.location.href = response.data;
                } else {
                    alert('Hệ thống đang bận. Vui lòng thử lại sau.');
                }
            }
        });
    }
</script>