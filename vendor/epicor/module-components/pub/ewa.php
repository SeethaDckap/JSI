<?php
require_once('utils/dbfixes/_setup.php');

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');


$assetRepo = $obj->get('Magento\Framework\View\Element\Template');
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script>
            parent.postMessage('{"height": 400,"width":600}', "*");
        </script>
        <style>
            label {
                float:left;
                clear:both;
                width:100px;
            }
            select { 
                width :250px;
            }
            fieldset {
                border:none;
            }
            li {
                list-style:none;
                padding:5px 0;
            }
            ul {
                padding-left:0;
            }
            input.price, #total {
                width:50px;
                text-align:right;
            }
            li.productTotal label {
                width:350px;
                padding-right:4px;
                text-align: right;
            }
        </style>

    </head>
    <body>
        <form method="post" id="ewaForm" action="<?php echo $_POST['ReturnURL']; ?>">
            <fieldset>
                <input type="hidden" value="<?php echo $_POST['PartNum']; ?>" name="SKU" />
                <legend>Product Configurator : Page 1 of 1</legend>
                <ul>
                    <li>
                        <label for="serverFamily">Server Family</label>
                        <select id="serverFamily" name="serverFamily">
                            <option value="Ess">Essential</option>
                            <option value="Enh">Enhanced</option>
                        </select>
                    </li>
                    <li>
                        <label for="serverType">Server Type</label>
                        <select id="serverType" name="serverType" onchange="update(this);">
                            <option value="0.00">Please Select an Option</option>
                            <option value="105.11">PowerEdge T105</option>
                            <option value="440.22">PowerEdge SC440</option>
                            <option value="840.33">PowerEdge 840</option>
                            <option value="300.44">PowerEdge T300</option>
                        </select>
                    </li>
                    <li class="serverOptions">
                        <label for="memory">Memory</label>
                        <select id="memory" name="memory" onchange="update(this);">
                            <option value="0.00">512MB DDR2 667Mhz 1x512MB</option>
                            <option value="15.00">512MB DDR2 800Mhz 1x512MB</option>
                            <option value="20.00">1GB DDR2 667Mhz 2x512MB</option>
                            <option value="25.00">1GB DDR2 800Mhz 2x512MB</option>
                            <option value="30.00">1GB DDR2 667Mhz 1x1GB</option>
                            <option value="45.00">1GB DDR2 800Mhz 1x1GB</option>
                            <option value="50.00">2GB DDR2 667Mhz 4x512MB</option>
                            <option value="55.00">2GB DDR2 800Mhz 4x512MB</option>
                            <option value="65.00">2GB DDR2 667Mhz 2x1GB</option>
                            <option value="70.00">2GB DDR2 800Mhz 2x1GB</option>
                        </select>
                    </li>
                    <li class="serverOptions">
                        <label for="controller">Controller</label>
                        <select id="controller" name="controller" onchange="update(this);">
                            <option value="0.00">Onboard SATA 1-2 Drives onnected to onboard SATA controller - No RAID</option>
                            <option value="10.00">SAS6iR (SATA/SAS Controller) supports 1-2 Drives - No RAID</option>
                            <option value="15.00">SAS6iR (SATA/SAS Controller) supports 2 Drives - RAID 0</option>
                            <option value="20.00">SAS6iR (SATA/SAS Controller) supports 2 Drives - RAID 1</option>
                        </select>
                    </li>
                    <li class="serverOptions">
                        <label for="disk1">Disk 1</label>
                        <select id="disk1" name="disk1" onchange="update(this);">
                            <option value="0.00">80GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="30.00">160GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="80.00">250GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="120.00">500GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="150.00">750GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="75.00">73GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="120.00">146GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="190.00">300GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                        </select>
                    </li>
                    <li class="serverOptions">
                        <label for="disk2">Disk 2</label>
                        <select id="disk2" name="disk2" onchange="update(this);">
                            <option value="0.00">None</option>
                            <option value="50.00">80GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="80.00">160GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="120.00">250GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="170.00">500GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="200.00">750GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="125.00">73GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="170.00">146GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="240.00">300GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                        </select>
                    </li>
                    <li class="serverOptions">
                        <label for="disk3">Disk 3</label>
                        <select id="disk3" name="disk3" onchange="update(this);">
                            <option value="0.00">None</option>
                            <option value="50.00">80GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="80.00">160GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="120.00">250GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="170.00">500GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="200.00">750GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="125.00">73GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="170.00">146GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="240.00">300GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                        </select>
                    </li>
                    <li class="serverOptions">
                        <label for="disk4">Disk 4</label>
                        <select id="disk4" name="disk4" onchange="update(this);">
                            <option value="0.00">None</option>
                            <option value="50.00">80GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="80.00">160GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="120.00">250GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="170.00">500GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="200.00">750GB 7.2K RPM SATA 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="125.00">73GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="170.00">146GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                            <option value="240.00">300GB 15K RPM Serial-Attach SCSI 3Gbps 3.5" Cabled Hard Drive</option>
                        </select>
                    </li>
                    <li>
                        <input id="submitBtn" type="submit"  value="Submit" />
                    </li>
                </ul>
            </fieldset>
        </form>
    </body>
</html>
