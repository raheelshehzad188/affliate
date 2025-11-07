<div id="dashboard" class="sap-section">
                        <?php
                            require_once "new_dashboard.php";

                        ?>
                            <h2>Dashboard</h2>
                            <div id="welcomeMsg">Welcome to london Aesthetics UK!</div>
                            <?php
                    $year = date('Y');
$month = date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$from_date = "$year-$month-01";
$to_date = "$year-$month-$daysInMonth";
$vendor = wp_get_current_user();

$comm = get_affiliate_commission_total($vendor->ID, $from_date, $to_date);
$clicks = get_clicks_by_date_range($vendor->ID, $from_date, $to_date);
        $pending_leads = get_pending_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
        $confirm_leads = get_confirmed_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
        $level1 = get_level_1_affiliates($vendor->ID, $from_date, $to_date);
        $level2 = get_level_2_affiliates($vendor->ID, $from_date, $to_date);
        $level3 = get_level_3_affiliates($vendor->ID, $from_date, $to_date);
                ?>
                                <div class="dashboard-row">
                                    <div class="card">
                                        <h2>Clicks</h2>
                                        <p>
                                            <?= $clicks ?>
                                        </p>
                                    </div>
                                    <div class="card">
                                        <h2>Commission</h2>
                                        <p>
                                            <?= $comm ?>
                                        </p>
                                    </div>
                                    <div class="card">
                                        <h2>Confirm Leads</h2>
                                        <p>
                                            <?= $confirm_leads ?>
                                        </p>
                                    </div>
                                    </div>
                                    <div class="dashboard-row">
                                    
                                    <div class="card">
                                        <h2>Level 1 Referals</h2>
                                        <p>
                                            <?= count($level1) ?>
                                        </p>
                                    </div>
                                    <div class="card">
                                        <h2>Level 2 Referals</h2>
                                        <p>
                                            <?= count($level2) ?>
                                        </p>
                                    </div>
                                    <div class="card">
                                        <h2>Level 3 Referals</h2>
                                        <p>
                                            <?= count($level3) ?>
                                        </p>
                                    </div>
                                </div>
                                <!--<div class="dashboard-row">-->

                                <!--</div>-->
                                <!--<h2>Clicks in Last 30 Days</h2>-->
                                <div id="barchart_values" style="width: 100%; height: 600px;"></div>



                        </div>