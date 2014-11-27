<?php
/**
 * 簡易見積シミュレータ - XmlSimulater
 * 
 * @package     XmlSimulater
 * @author      Y.Yajima <yajima@hatchbit.jp>
 * @copyright   2014, HatchBit & Co.
 * @license     http://www.hatchbit.jp/resource/license.html
 * @link        http://www.hatchbit.jp
 * @since       Version 2.0
 * @filesource
 */
/*====================
  DEFINE
  ====================*/
require dirname(__FILE__).'/includes/start.php';
//テンプレート
$template = file_get_contents('template.html');
$result_table = "";

/*====================
  BEFORE ACTIONS
  ====================*/

/*====================
  MAIN ACTIONS
  ====================*/
if(isset($_GET['mode'])){
    switch($_GET['mode']){
        case "cal":
            $gross = 0;
            $total = 0;
            $price = intval($_GET['price']);
            $displacement = intval($_GET['displacement']);
            $month = intval($_GET['month']);
            $xml = new SimpleXMLElement(file_get_contents('./includes/db.xml'));
            
            $data = array();
            
            $data['price'] = number_format($price).'円';
            $total += $price;
            
            $agent_temp = calc1($xml->agentpay->unit, $xml->agentpay->value, $price);
            if($agent_temp < 35000) $agent_temp = 35000;
            $data['agentpay'] = number_format($agent_temp,0).'円';
            $total += $agent_temp;
            
            $shippingpay_temp = calc1($xml->shippingpay->unit, $xml->shippingpay->value, $price);
            $data['shippingpay'] = number_format($shippingpay_temp).'円';
            $total += $shippingpay_temp;
            
            $accountpay_temp = calc1($xml->accountpay->unit, $xml->accountpay->value, $price);
            $data['accountpay'] = number_format($accountpay_temp).'円';
            $total += $accountpay_temp;
            
            $accountstamppay_temp = calc1($xml->accountstamppay->unit, $xml->accountstamppay->value, $price);
            $data['accountstamppay'] = number_format($accountstamppay_temp).'円';
            $total += $accountstamppay_temp;
            
            $parkingcertificatepay_temp = calc1($xml->parkingcertificatepay->unit, $xml->parkingcertificatepay->value, $price);
            $data['parkingcertificatepay'] = number_format($parkingcertificatepay_temp).'円';
            $total += $parkingcertificatepay_temp;
            
            $parkingcertificatestamppay_temp = calc1($xml->parkingcertificatestamppay->unit, $xml->parkingcertificatestamppay->value, $price);
            $data['parkingcertificatestamppay'] = number_format($parkingcertificatestamppay_temp).'円';
            $total += $parkingcertificatestamppay_temp;
            
            $numberplate_temp = calc1($xml->numberplate->unit, $xml->numberplate->value, $price);
            $data['numberplate'] = number_format($numberplate_temp).'円';
            $total += $numberplate_temp;
            
            $reciclepay_temp = calc1($xml->reciclepay->unit, $xml->reciclepay->value, $price);
            $data['reciclepay'] = number_format($reciclepay_temp).'円';
            $total += $reciclepay_temp;
            
            foreach($xml->automobiletax->cc as $cc){
                //var_dump($cc);
                $thisattr = (string) $cc->attributes()->id;
                if(strpos($thisattr, "over") !== false){
                    $thiscc = 10000000000;
                }else{
                    $thiscc = intval(str_replace("cc","",$cc['id']));
                }
                if($displacement <= $thiscc){
                    if($month == 1) $thistaxrate = $cc->m1;
                    if($month == 2) $thistaxrate = $cc->m2;
                    if($month == 3) $thistaxrate = $cc->m3;
                    if($month == 4) $thistaxrate = $cc->m4;
                    if($month == 5) $thistaxrate = $cc->m5;
                    if($month == 6) $thistaxrate = $cc->m6;
                    if($month == 7) $thistaxrate = $cc->m7;
                    if($month == 8) $thistaxrate = $cc->m8;
                    if($month == 9) $thistaxrate = $cc->m9;
                    if($month == 10) $thistaxrate = $cc->m10;
                    if($month == 11) $thistaxrate = $cc->m11;
                    if($month == 12) $thistaxrate = $cc->m12;
                    break;
                }else{
                    continue;
                }
            }
            $automobiletax_temp = intval($thistaxrate);
            $data['automobiletax'] = number_format($automobiletax_temp).'円';
            $total += $automobiletax_temp;
            
            $gross = $price + $agent_temp;
            $tax_temp = calc1($xml->tax->unit, $xml->tax->value, $gross);
            $data['tax'] = number_format($tax_temp).'円';
            $total += $tax_temp;
            
            $data['total'] = number_format($total).'円';
            $result_table = resultTable($data);
            break;
        default:
            break;
    }
    //var_dump($data);
}


/*====================
  AFTER ACTIONS
  ====================*/
//車両落札金額
$value_price = 0;
if(isset($_GET['price'])){
    $value_price = intval($_GET['price']);
}else{
    $value_price = 0;
}
//排気量
$value_displacement = 0;
if(isset($_GET['displacement'])){
    $value_displacement = intval($_GET['displacement']);
}else{
    $value_displacement = 0;
}
//月のオプション値
$option_month = '';
$opt_temp = "<option value='%s' %s>%s</option>¥n";
for($i=1;$i<13;$i++){
    if(isset($_GET['month'])){
        $thisMonth = intval($_GET['month']);
    }else{
        $thisMonth = date("n");
    }
    if($i == $thisMonth){
        $option_month .= sprintf($opt_temp, $i, "selected='selected'", strval($i.''));
    }else{
        $option_month .= sprintf($opt_temp, $i, "", strval($i.''));
    }
}
//変数処理
$template = str_replace("<!--[[VALUE_PRICE]]-->", $value_price, $template);
$template = str_replace("<!--[[VALUE_DISPLACEMENT]]-->", $value_displacement, $template);
$template = str_replace("<!--[[OPTION_MONTH]]-->", $option_month, $template);
$template = str_replace("<!--[[RESULT]]-->", $result_table, $template);

echo $template;

/*====================
  FUNCTIONS
  ====================*/
function calc1($unit,$value,$base = 0) {
    $result = 0;
    if($unit == "yen"){
        $result = floatval($value);
    }elseif($unit == "per"){
        $result = $base / 100 * floatval($value);
    }
    return $result;
}

function resultTable($data) {
    $result = "";
    $table_temp = '
    <table id="resultTable">
        <tbody>
            <tr>
                <th>車両落札価格</th>
                <td class="description">&nbsp;</td>
                <td>##price##</td>
            </tr>
            <tr>
                <th>オークション代行手数料</th>
                <td class="description">落札額の3.5％<br />(35,000円以下は一律35,000円)</td>
                <td>##agentpay##</td>
            </tr>
            <tr>
                <th>消費税</th>
                <td class="description">上記合計金額の8％</td>
                <td>##tax##</td>
            </tr>
            <tr>
                <th>陸送費用</th>
                <td class="description">※USS大阪会場～当社の場合<br />場所により変動します</td>
                <td>##shippingpay##</td>
            </tr>
            <tr>
                <th>名義変更費用</th>
                <td class="description">地域により変動します</td>
                <td>##accountpay##</td>
            </tr>
            <tr>
                <th>印紙代</th>
                <td class="description">&nbsp;</td>
                <td>##accountstamppay##</td>
            </tr>
            <tr>
                <th>車庫証明申請費用</th>
                <td class="description">&nbsp;</td>
                <td>##parkingcertificatepay##</td>
            </tr>
            <tr>
                <th>印紙代</th>
                <td class="description">地域により変動します</td>
                <td>##parkingcertificatestamppay##</td>
            </tr>
            <tr>
                <th>ナンバープレート代</th>
                <td class="description">地域により変動します</td>
                <td>##numberplate##</td>
            </tr>
            <tr>
                <th>自動車税</th>
                <td class="description">&nbsp;</td>
                <td>##automobiletax##</td>
            </tr>
            <tr>
                <th>リサイクル料</th>
                <td class="description">車種により変動します</td>
                <td>##reciclepay##</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>合計</th>
                <td class="description">車検無しのお車の場合は別途、<br />車検費用が必要となります</td>
                <td>##total##</td>
            </tr>
        </tfoot>
    </table>
    ';
    $resultbody = $table_temp;
    foreach($data as $key => $val){
        $resultbody = str_replace("##".$key."##", $val, $resultbody);
    }
    $result = $resultbody;
    return $result;
}
?>