<?php if ($total >= (int)$this->get_option('installment_amount')) {?>
    <div style="margin-bottom: 15px; background: white;padding: 7px;" class="payon_box payon_ins_wrap collapse panel" id="collapseOne" aria-labelledby="headingOne" data-parent="#accordion">
        <div class="payon_label payon_ins_title">Chọn ngân hàng trả góp</div>
        <div class="payon-listbank">
            <ul class="list">
                <?php foreach ($listBanks as $bank): ?>
                    <li>
                        <label data-code='<?php esc_html_e($bank['code']); ?>' data-card='<?php esc_html_e(json_encode($bank["installment_card_type"])); ?>' title='<?php esc_html_e($bank['full_name']); ?>' >
                            <input value='<?php esc_html_e($bank['code']); ?>' name='payon_bank' type='radio'>
                            <span id='card-<?php esc_html_e($bank['code']); ?>' class='bg-white' onclick='devClick("<?php esc_html_e($bank["code"]); ?>");' data-card='<?php esc_html_e(json_encode($bank['installment_card_type'])) ?>' data-name='<?php esc_html_e($bank["full_name"]) ; ?>' data-cycle='<?php esc_html_e(json_encode($bank['cycle'])) ; ?>'><img data-lazyloaded='1' src='<?php esc_html_e( $bank['logo']); ?>' data-src='<?php esc_html_e($bank['logo']) ; ?>' alt='<?php esc_html_e($bank['full_name']) ; ?>' data-ll-status='loaded' class='entered litespeed-loaded'></span>
                            <input type="hidden" id="rule-bank-<?php esc_html_e($bank['code']); ?>" value="<?php esc_html_e($bank['installment_rule']); ?>">
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div id="mess-bank" style="color: #D57B00;"></div>
        <div class="payon_box payon_card_box">
            <div class="payon_label payon_ins_title">Chọn loại thẻ</div>
            <div class="payon-listbank">
                <ul class="list">
                    <li>
                        <label class="bg-white" data-code="visa" title="visa">
                            <input value="visa" name="payon_card" type="radio">
                            <span onclick="checkData('visa')"><img src="<?php echo plugins_url('assets/images/visa.png', plugin_dir_path(__FILE__)); ?>" alt="visa"></span>
                        </label>
                    </li>
                    <li>
                        <label class="bg-white" data-code="mastercard" title="mastercard">
                            <input value="mastercard" name="payon_card" type="radio">
                            <span onclick="checkData('mastercard')"><img src="<?php echo plugins_url('assets/images/master.png', plugin_dir_path(__FILE__)); ?>" alt="mastercard"></span>
                        </label>
                    </li>
                    <li>
                        <label class="bg-white" data-code="jcb" title="jcb">
                            <input value="jcb" name="payon_card" type="radio">
                            <span onclick="checkData('jcb')"><img src="<?php echo plugins_url('assets/images/jcb.png', plugin_dir_path(__FILE__)); ?>" alt="jcb"></span>
                        </label>
                    </li>
                </ul>
            </div>
            <p id="loading">Đang tải dữ liệu...</p>
        </div>
        <div class="payon_ins_box payon_cycle_box" style="display: none">
            <div>
                <label class="payon_ins_title">Chọn kỳ trả góp</label>
                <div class="list_radio_style payon_cycle_wrap">
                    <label onclick="checkDataIns(3)" data-month-cycle="3">
                        <input type="radio" name="payon_cycle" class="payon_cycle_3" value="3">
                        <span><strong>3</strong> Tháng</span>
                    </label>
                    <label data-month-cycle="6" onclick="checkDataIns(6)">
                        <input type="radio" name="payon_cycle" class="payon_cycle_6" value="6">
                        <span><strong>6</strong> Tháng</span>
                    </label>
                    <label  data-month-cycle="9" onclick="checkDataIns(9)">
                        <input type="radio" name="payon_cycle" class="payon_cycle_9" value="9">
                        <span><strong>9</strong> Tháng</span>
                    </label >
                    <label data-month-cycle="12" onclick="checkDataIns(12)">
                        <input type="radio" name="payon_cycle" class="payon_cycle_12" value="12">
                        <span><strong>12</strong> Tháng</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="payon_ins_box total_payon_wrap" style="display: none;">
            <div>
                <ul>
                    <li style="display: block;">
                        <div class="row" style="margin: 0;border-bottom: 1px solid #ddd;">
                            <div class="col" style="width:50%; text-align:left; padding: 0">
                                <strong style="padding: 10px;">Chênh lệch so với trả thẳng</strong>
                            </div>
                            <div class="col" style="width:50%; text-align:right; padding: 0">
                                <span class="chenhlech_payon_3 month_3" style="display: none;"></span>
                                <span class="chenhlech_payon_6 month_6" style="display: none;"></span>
                                <span class="chenhlech_payon_9 month_9" style="display: none;"></span>
                                <span class="chenhlech_payon_12 month_12" style="display: none;"></span>
                            </div>
                        </div>
                    </li>
                    <li style="display: block;">
                        <div class="row" style="margin: 0;border-bottom: 1px solid #ddd;">
                            <div class="col" style="width:50%; text-align:left; padding: 0">
                                <strong style="padding: 10px;">Tổng tiền trả góp</strong>
                            </div>
                            <div class="col" style="width:50%; text-align:right; padding: 0">
                                <span class="total_payon_3 month_3" style="display: none;"></span>
                                <span class="total_payon_6 month_6" style="display: none;"></span>
                                <span class="total_payon_9 month_9" style="display: none;"></span>
                                <span class="total_payon_12 month_12" style="display: none;"></span>
                            </div>
                        </div>
                    </li>
                    <li style="display: block;">
                        <div class="row" style="margin: 0; border-bottom: 1px solid #ddd;">
                            <div class="col" style="width:50%; text-align:left; padding: 0">
                                <strong style="padding: 10px;">Trả góp mỗi tháng</strong>
                            </div>
                            <div class="col" style="width:50%; text-align:right; padding: 0">
                                <span class="total_month_3 month_3" style="display: none;"></span>
                                <span class="total_month_6 month_6" style="display: none;"></span>
                                <span class="total_month_9 month_9" style="display: none;"></span>
                                <span class="total_month_12 month_12" id="total_month_12" style="display: none;"></span>
                            </div>
                        </div>
                    </li>
                </ul>
                <input type="hidden" id="fee-by-month" name="fee-by-month" value="">
                <input type="hidden" id="total-installment" name="total-installment" value="">
                <input type="hidden" id="money-different" name="money-different" value="">
                <input type="hidden" id="total-pro" name="total-pro" value="<?php echo $total; ?>">
            </div>
        </div>
    </div>
    <input value="" name="bank_code" id="bank_code" type="hidden">
<?php wp_enqueue_script('jquery'); ?>
    <script>
        var payon_bank;
        var payon_card;
        var payon_cycle;
        var cycle_selected;
        var data_name;
        var cycled = 0;
        function devClick(data){
            jQuery("#loading").hide();
            jQuery(".payon_cycle_box").css("display", "none");
            var myDiv = document.querySelector("#card-"+data);
            var data_card = myDiv.dataset.card;
            payon_cycle = myDiv.dataset.cycle;
            payon_bank = data;
            jQuery("#mess-bank").html();
            jQuery("#mess-bank").css("display", "none");
            jQuery(".total_payon_wrap").css("display", "none");
            var mess_bank = jQuery('#rule-bank-'+data).val();
            if(mess_bank){
            jQuery("#mess-bank").html(mess_bank);
            jQuery("#mess-bank").css("display", "block");
            }
            if (data_card) {
                data_card = JSON.parse(data_card);
                jQuery(".payon_card_box").css("display", "block");
                jQuery(".devvn_card_click").each(function() {
                    var data_card_box = jQuery(this).attr("data-code");
                    if(jQuery.inArray(data_card_box, data_card) !== -1) {
                        jQuery(this).removeAttr("hidden");
                    }else{
                        jQuery(this).prop("hidden", true);
                    }
                });
                jQuery("input[name=payon_card]").prop("checked", false);
            }
            
        }
        
        function checkData(card) {
            jQuery(".total_payon_wrap").css("display", "none");
            jQuery(".payon_cycle_box").css("display", "none");
            payon_card =  card;
            var load_payon_cycle = JSON.parse(payon_cycle);
            for (var i = 0; i < load_payon_cycle.length; i++){
                
                let collection = document.getElementsByClassName("month_"+load_payon_cycle[i]);
                    for (let i = 0; i < collection.length; i++) {
                        collection[i].style.display = "none";
                        collection[i].text = "";
                    }
            }
            if (payon_bank && payon_card && payon_cycle) {
                var payon_cycle_data = JSON.parse(payon_cycle);
                payon_cycle_data = JSON.stringify(payon_cycle_data);
                jQuery("#loading").show();
                var amount = jQuery("#total-pro").val();
                amount = parseInt(amount);
                var url = "<?php echo get_rest_url( null, "/payon/fee" ) ?>";
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
                    success: function (response) {
                        
                        var load_payon_cycle = JSON.parse(payon_cycle);
                        for (var i = 0; i < load_payon_cycle.length; i++){
                            if(load_payon_cycle[i] == 3){
                                jQuery(".chenhlech_payon_3").html(response.different.m3);
                                jQuery(".total_payon_3").html(response.totalInstallment.m3);
                                jQuery(".total_month_3").html(response.feeByMonth.m3);
                            }
                            if(load_payon_cycle[i] == 6){
                                jQuery(".chenhlech_payon_6").html(response.different.m6);
                                jQuery(".total_payon_6").html(response.totalInstallment.m6);
                                jQuery(".total_month_6").html(response.feeByMonth.m6);
                            }
                            if(load_payon_cycle[i] == 9){
                                jQuery(".chenhlech_payon_9").html(response.different.m9);
                                jQuery(".total_payon_9").html(response.totalInstallment.m3);
                                jQuery(".total_month_9").html(response.feeByMonth.m9);
                            }
                            if(load_payon_cycle[i] == 12){
                                jQuery(".chenhlech_payon_12").html(response.different.m12);
                                jQuery(".total_payon_12").html(response.totalInstallment.m12);
                                jQuery(".total_month_12").html(response.feeByMonth.m12);
                            }
                        }
                        jQuery("#loading").hide();
                        jQuery("#payon_cycle").val(payon_cycle);
                        jQuery("#cycle").val(payon_cycle);
                        if(cycled > 0){
                            checkDataIns(cycled);
                        }
                        checkDataCycle(payon_bank);
                    },
                });
            }
        }
        function checkDataCycle(data){
            jQuery(".payon_cycle_box").css("display", "block");
            var myDiv = document.querySelector("#card-"+data);
            var data_cycle =  myDiv.dataset.cycle;
            data_cycle = JSON.parse(data_cycle);
            jQuery(".payon_prepaid_cycle").each(function() {
                var data_month_cycle = jQuery(this).attr("data-month-cycle");
                if(jQuery.inArray(parseInt(data_month_cycle), data_cycle) !== -1) {
                    jQuery(this).removeAttr("hidden");
                }else{
                    jQuery(this).prop("hidden", true);
                }
            });
        }

        function checkDataIns(data) {
            var myDiv = document.querySelector("#card-"+payon_bank);
            jQuery(".total_payon_wrap").css("display", "block");
            data_name = myDiv.dataset.name;
            let feemonth = jQuery(".total_month_"+data).html();
            let total_installment = jQuery(".total_payon_"+data).html();
            let different = jQuery(".chenhlech_payon_"+data).html();
            jQuery("#fee-by-month").val(feemonth);
            jQuery("#total-installment").val(total_installment);
            jQuery("#money-different").val(different);
            var load_payon_cycle = JSON.parse(payon_cycle);
            for (var i = 0; i < load_payon_cycle.length; i++){
                
                let collection = document.getElementsByClassName("month_"+load_payon_cycle[i]);
                if(load_payon_cycle[i] == data){
                    for (let i = 0; i < collection.length; i++) {
                        collection[i].style.display = "block";
                        
                    }
                } else {
                    for (let i = 0; i < collection.length; i++) {
                        collection[i].style.display = "none";
                    }
                }
            }
            const collection = document.getElementsByClassName("example");
            cycled = data;
            
        }
    </script>
<?php } else {?>
    <script>
    document.getElementById("payment_method_installment").checked = false;
    document.getElementById("payment_method_installment").disabled = true;
    </script>
<?php } ?>