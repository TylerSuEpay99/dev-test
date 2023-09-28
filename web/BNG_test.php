<?php
/**
 * 20230516 修正login判斷值
 * 20230517 新增gameid參數、gameid回傳值
 * 20230518 刪除deposit1，合併至deposit & 修正部分回傳參數($msg)
 */
ob_start();
session_start();
error_reporting(1);
date_default_timezone_set("Asia/Taipei");
header('Content-Type: application/json;charset=utf-8');
// include($_SERVER['DOCUMENT_ROOT'].'/func/db_conn.php');
// include($_SERVER['DOCUMENT_ROOT'].'/func/Function.php');
// include($_SERVER['DOCUMENT_ROOT'].'/func/game_account_set.php'); //遊戲帳號加密

/**
 * 遊戲相關資訊
 * 遊戲名稱 : 
 * API手冊: 
 * 有否提供遊戲列表api : 
 */

define("API_GAMENAME","BNG電子"); // 遊戲名稱 gameF
define("API_CODENAME","BNG"); // 遊戲簡稱 (英文) 檔名,tmClass
define("API_TYPE","電子"); // 遊戲類型(真人、電子、體育、棋牌、鬥雞、彩票) gameT

define("APIURL","https://gate-stage.betsrv.com/op/"); // 正式   測試   

define("APITOKEN","imNdMFnFQL"); // 正式   測試   
define("APIKEY",""); // 正式   測試   
define("APIID",""); // 正式   測試   

define("CUR", "TWD");
define("LANG", "zh-TW");

$request = new Request($_REQUEST);

// Request 封裝
class Request {

    public function __construct(array $params = [])
    {
        foreach($params as $key => $value) {
            $this->$key = $value;
        }
    }

    //如果成員變數沒有該值則觸發設定
    public function __set($name, $value) 
    {
        return $this->$name = $value;
    }

    //如果存取未設定變數會處發顯示訊息
    public function __get($name)
    {
        return "變數".$name."未設定";
    }
}


switch($request->Mode)
{
    case "register":
        $obj = create($request);
        echo json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit();
    break;
    case "balance":
        $obj = balance($request);
        echo json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit();
    break;
    case "in_money":
        $obj = deposit($request);
        echo json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit();
    break;    
    case "out_money":
        $obj = withdraw($request);
        echo json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit();
    break;
    case "record":
        $rr = explode('/',$_SERVER['PHP_SELF']);
        $rr_str = $rr[count($rr)-2].'/'.$rr[count($rr)-1];
        echo $rr_str.'<br>';
        echo '抓'.API_GAMENAME.'遊戲紀錄<br>';
        echo '<title>'.API_GAMENAME.'</title>';
        echo API_GAMENAME.'<br>';
        print_o('查詢時間:'.date('Y-m-d H:i:s'));

        print_o("===============撈取遊戲紀錄==================");
        $obj = record();

        exit();
    break;

    case "get_all_back_to_main":
        $obj = get_all_back_to_main($request);
        echo json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit();
    break;

    case "game":
        $obj = gamelist();
        echo json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit();
    break;

}

function gamelist()
{
    /**查詢遊戲列表
     * [POST] 
     */
    $url = APIURL;
    $params = [
        '' => '',
        '' => '',
    ];
    $obj = post_curl($url, $params);
    return $obj;
}

// 註冊
function create($request)
{
    /**
     * 會員創建
     * [POST] op/89b-stage/wallet/select_money/create_player/
     */
    $url = APIURL . '89b-stage/wallet/select_money/create_player/';
    $params = [
        'api_token' => APITOKEN,
        'player_id' => $request->mid, //玩家标识符，填入mid?
        'currency' => 'TWD', //幣值
        'mode' => 'REAL', //遊戲模式， ("REAL", "FUN")。預設為 "REAL"
        'is_test' => 'true', // 測試時true
        'brand' => 'wl', //站點功能
    ];
    $obj = post_curl($url, $params);
    print_r($obj);exit;
    // $h = login($request);
    // return $h;

}

// 登入
function login($request)
{
    /**
     * 會員登入 
     * [POST] 
     */
    $url = APIURL;
    $params = [
        '' => '',
        '' => '',
    ];
    $obj = get_curl($url, $params);
    if($obj['status'] == 'success') // 根據遊戲商回傳調整: api狀態
    {
        if($request->wallet_method == 'all_money') // 免轉錢包
        {
            $de = deposit($request);
            
            $msg = array(
                'code' => '00',
                'msg' => 'Success',
                'type' => 'login',
                'game' => API_CODENAME,
                'gameid' => $request->gameid,
                'wallet_method' => 'all_money',
                'mid' => $request->mid,
                'login_url' => $obj['data']['url'], // 根據遊戲商回傳調整: 登入url
            );
            
        }
        elseif($request->wallet_method == 'select_money') // 轉帳錢包
        {
            $msg = array(
                'code' => '00',
                'msg' => 'Success',
                'type' => 'login',
                'game' => API_CODENAME,
                'gameid' => $request->gameid,
                'wallet_method' => 'select_money',
                'mid' => $request->mid,
                'login_url' => $obj['data']['url'], // 根據遊戲商回傳調整: 登入url
            );
        }
    }
    else
    {
        $msg = array(
            'code' => '12',
            'msg' => "Game login failed",
            'type' => 'login',
            'game' => API_CODENAME,
            'gameMsg' => $obj['data']['message'], // 根據遊戲商回傳調整: errorMsg/errorCode
        );
    }
    return $msg;

}

function balance($request)
{
    /**
     * 取得會員錢包餘額
     * [POST] 
     */
    $url = APIURL;
    $params = [
        '' => '',
        '' => '',
    ];
    $obj = post_curl($url, $params);
    if($obj['player_id'] != '' && $obj['error'] == '') // 根據遊戲商回傳調整: api狀態
    {
        $msg = array(
            'code' => '00',
            'msg' => "Success",
            'type' => 'balance',
            'game' => API_CODENAME,
            'mid' => $request->mid,
            'game_balance' => $obj['balance'], // 遊戲錢包餘額
        );
    }else{
        $msg = array(
            'code' => '14',
            'msg' => "Check customer’s balance failed",
            'type' => 'balance',
            'game' => API_CODENAME,
            'mid' => $request->mid,
            'gameMsg' => $obj['error'], // 根據遊戲商回傳調整: errorMsg/errorCode
        );
    }
    return $msg;

}

function deposit($request)
{
    /**
     * 會員轉帳 : 存款到玩家遊戲帳戶中。
     * [POST] 
     */
    $tSN = 'T'.date('m').date('d').date('H').date('i').date('s').rand(1,1000);
    $csbalance = csBalance($request->mid); // 會員主錢包餘額
    $amount = $request->amount;
    if($request->wallet_method == 'all_money') // 免轉錢包
    {
        $type = '一鍵轉入遊戲';
        $amount = $csbalance;
    }
    elseif($request->wallet_method == 'select_money') // 轉帳錢包
    {
        $type = '指定金額('.$amount.')轉入遊戲';
        $amount = $request->amount;
    }

    if($amount <= $csbalance)
    {
        $url = APIURL;
        $params = [
            '' => '',
            '' => '',
        ];
        $obj = post_curl($url, $params);
        if($obj['error'] == '' && $obj['uid'] != "" && $csbalance > 0) // 根據遊戲商回傳調整: api狀態
        {
            // $ps_type = API_GAMENAME.$type;
            // $mid_tmp= str_replace(' ','%20',diset_account($request->mid));
            // $amount1 = $amount * -1;
            // $where_do = 'api_test=>'.API_CODENAME.'_test.php[deposit]';
            // $url = BALANCE_CALL_URL."/pipi_api.php?Mode=add_money&mid={$mid_tmp}&amount={$amount1}&ps={$ps_type}&do_man={$gm_name}&where_do={$where_do}&ip={$ip}";					
            // $obj1 = json_decode( file_get_contents($url) ,true);
            
    
            // $AddDB = SetAddDB($AddDB,'sid','',0); //系統ID
            // $AddDB = SetAddDB($AddDB,'tSN',$tSN,1); //錢包轉帳編號
            // $AddDB = SetAddDB($AddDB,'mid',diset_account($request->mid),1); //帳號ID
            // $AddDB = SetAddDB($AddDB,'tmTime',date('Y-m-d H:i:s'),1); //轉帳時間
            // $AddDB = SetAddDB($AddDB,'tmType','轉出',1); //轉帳型態
            // $AddDB = SetAddDB($AddDB,'tmSource','main',1); //轉入來源
            // $AddDB = SetAddDB($AddDB,'tmClass',API_CODENAME,1); //轉帳分類
            // $AddDB = SetAddDB($AddDB,'tmMain',abs($amount),1); //轉帳金額
            // $AddDB = SetAddDB($AddDB,'from_start',$csbalance,1); //主錢包$
            // $AddDB = SetAddDB($AddDB,'from_end',$csbalance-abs($amount),1); //主錢包$-指定轉入金額
            // $AddDB = SetAddDB($AddDB,'to_start',$obj['balance_before'],1); //before遊戲錢包
            // $AddDB = SetAddDB($AddDB,'to_end',$obj['balance_after'],1); //after遊戲錢包
            // $AddDB = SetAddDB($AddDB,'lastUpdateMan',$t_mid,1); //最後異動人員
            // $AddDB = SetAddDB($AddDB,'lastUpdateTime',date('Y-m-d H:i:s'),1); //最後異動時間
            // $AddDB = SetAddDB($AddDB,'ip',$ip,1); //最後異動IP
            // $addMsg = AddDB('transferMoney',$AddDB[0],$AddDB[1],'0');
    
            $msg = array(
                'code' => '00',
                'msg' => 'Success',
                'type' => 'deposit',
                'game' => API_CODENAME,
                'mid' => $request->mid,
                'wallet_method' => $request->wallet_method,
                'amount' => $amount,
                'main_wallet' => $csbalance-abs($amount), // 轉入後主錢包額度
                'game_balance' => $obj['balance_after'], // 轉入後遊戲錢包額度
            );
            
        }
        elseif($csbalance <= 0)
        {
            $msg = array(
                'code' => '16',
                'msg' => 'Customer’s mainWallet balance is less than 0',
                'type' => 'deposit',
                'game' => API_CODENAME,
                'mid' => $request->mid,
                'gameMsg' => $obj['error'],
            );
        }
        else
        {
            $msg = array(
                'code' => '13',
                'msg' => 'Transaction failed',
                'type' => 'deposit',
                'game' => API_CODENAME,
                'mid' => $request->mid,
                'gameMsg' => $obj['error'], // 遊戲商回傳的error msg/error code
            );
        }
    }
    else
    {   
        $msg = array(
            'code' => '17',
            'msg' => "Customer’s mainWallet balance is not enough or customer cannot be found",
            'type' => 'deposit',
            'game' => API_CODENAME,
            'mid' => $request->mid,
        );
    }


    return $msg;

}



function withdraw($request)
{
    /**
     * 會員轉帳 : 存款到玩家遊戲帳戶中。
     * [POST] 
     */
    $tSN = 'T'.date('m').date('d').date('H').date('i').date('s').rand(1,1000);
    $csbalance = csBalance($request->mid); // 會員主錢包餘額
    $money_obj = balance($request); // 取得遊戲錢包餘額
    if($request->Mode == 'out_money') // 指定金額轉帳
    {
        $type = '指定金額('.$amount.')轉出遊戲';
        $amount = $request->amount;
    }
    elseif($request->Mode == 'get_all_back_to_main') // 全數金額轉帳
    {
        $type = '一鍵轉出遊戲';
        $amount = $money_obj['game_balance'];
    }

    $url = APIURL;
    $params = [
        '' => '',
        '' => '',
    ];
    $obj = post_curl($url, $params);
    if($obj['error'] == '' && $obj['uid'] != "" && $amount > 0) // 遊戲商回傳值判斷
    {
        // $ps_type = API_GAMENAME.$type;
        // $mid_tmp= str_replace(' ','%20',diset_account($request->mid));
        // $amount1 = abs($amount);
        // $where_do = 'newb9c=>api_test=>'.API_CODENAME.'_test.php['.$request->Mode.']';
        // $url = BALANCE_CALL_URL."/pipi_api.php?Mode=add_money&mid={$mid_tmp}&amount={$amount1}&ps={$ps_type}&do_man={$gm_name}&where_do={$where_do}&ip={$ip}";					
        // $obj1 = json_decode( file_get_contents($url) ,true);
        

        // $AddDB = SetAddDB($AddDB,'sid','',0); //系統ID
        // $AddDB = SetAddDB($AddDB,'tSN',$tSN,1); //錢包轉帳編號
        // $AddDB = SetAddDB($AddDB,'mid',diset_account($request->mid),1); //帳號ID
        // $AddDB = SetAddDB($AddDB,'tmTime',date('Y-m-d H:i:s'),1); //轉帳時間
        // $AddDB = SetAddDB($AddDB,'tmType','轉入',1); //轉帳型態
        // $AddDB = SetAddDB($AddDB,'tmSource',API_CODENAME,1); //轉入來源
        // $AddDB = SetAddDB($AddDB,'tmClass','main',1); //轉帳分類
        // $AddDB = SetAddDB($AddDB,'tmMain',abs($amount),1); //轉帳金額
        // $AddDB = SetAddDB($AddDB,'from_start',$obj['balance_before'],1); //before遊戲錢包
        // $AddDB = SetAddDB($AddDB,'from_end',$obj['balance_after'],1); //after遊戲錢包
        // $AddDB = SetAddDB($AddDB,'to_start',$csbalance,1); //主錢包$
        // $AddDB = SetAddDB($AddDB,'to_end',$csbalance+abs($amount),1); //主錢包$+遊戲轉出的$
        // $AddDB = SetAddDB($AddDB,'lastUpdateMan',$t_mid,1); //最後異動人員
        // $AddDB = SetAddDB($AddDB,'lastUpdateTime',date('Y-m-d H:i:s'),1); //最後異動時間
        // $AddDB = SetAddDB($AddDB,'ip',$ip,1); //最後異動IP
        // $addMsg = AddDB('transferMoney',$AddDB[0],$AddDB[1],'0');

        $msg = array(
            'code' => '00',
            'msg' => 'Success',
            'type' => 'withdraw',
            'game' => API_CODENAME,
            'mid' => $request->mid,
            'amount' => $amount,
            'main_wallet' => $csbalance+abs($amount), // 轉入後主錢包額度
            'game_balance' => $obj['balance_after'], // 轉入後遊戲錢包額度
        );
    }else{
        $msg = array(
            'code' => '13',
            'msg' => 'Transaction failed',
            'type' => 'withdraw',
            'game' => API_CODENAME,
            'mid' => $request->mid,
            'gameMsg' =>  $obj['error'],
        );
    }

    return $msg;

}

function get_all_back_to_main($request)
{
    
    $money_obj = balance($request); // 取得遊戲錢包餘額
    $csbalance = csBalance($request->mid); // 會員主錢包餘額
    if($money_obj['game_balance'] > 0 && $money_obj['msg'] == 'Success')
    {
        $money = $money_obj['game_balance'];
        $obj = withdraw($request); //轉出

        if($money > 0 && $obj['code'] == '00' && $obj['msg'] == 'Success')
        {
            $main_wallet = $csbalance+$money;
            $msg = array(
                'code' => '00',
                'msg' => 'Success',
                'type' => 'get_all_back_to_main',
                'game' => API_CODENAME,
                'mid' => $request->mid,
                'main_wallet' => $main_wallet, // 轉入後主錢包額度
            );
        }
        elseif($obj['msg'] != 'Success')
        {
            $msg = $obj;
            $tmp = $request->mid.API_GAMENAME.'一鍵取回錢包轉帳失敗'. json_encode($obj);
            // $tmp = str_replace("'",'"',$tmp);
            // $AddDB = SetAddDB($AddDB,'sid','',0); //系統ID
            // $AddDB = SetAddDB($AddDB,'tm_str',$tmp,1); //暫存內容
            // $addMsg = AddDB('tmp_json',$AddDB[0],$AddDB[1],'0');
        }

    }
    elseif($money_obj['msg'] != 'Success')
    {
        $msg = $obj;
        $tmp = $request->mid.API_GAMENAME.'一鍵取回查看會員錢包失敗';
        // $tmp = str_replace("'",'"',$tmp);
        // $AddDB = SetAddDB($AddDB,'sid','',0); //系統ID
        // $AddDB = SetAddDB($AddDB,'tm_str',$tmp,1); //暫存內容
        // $addMsg = AddDB('tmp_json',$AddDB[0],$AddDB[1],'0');
    }
    else
    {
        $msg = array(
            'code' => '15',
            'msg' => 'Customer’s gameWallet balance is 0',
            'type' => 'get_all_back_to_main',
            'game' => API_CODENAME,
            'mid' => $request->mid,
        );
    }
    return $msg;
}

function record()
{
    /**遊戲紀錄
     * [POST] 
     */
    // $sql = "SELECT gid FROM gameRecord_tmp WHERE gameF = 'BNG電子' AND gTime >= '".date('Y-m-d H:i:s',strtotime('-1 hour'))."'";
    // $res = mysql_query($sql) or die("資料查詢失敗,請聯絡管理員,".mysql_error());
    // $num = mysql_num_rows($res);
    // while($row = mysql_fetch_array($res)){
    //     $all_gid[] = $row['gid'];
    // }
    // print_o('我方已有注單:'.count($all_gid));

    $url = APIURL;
    $params = [
        ''=> '',
        ''=> '',
    ];
    $obj = post_curl($url, $params);

    print_r('遊戲商傳回: '.count($obj['items'])); // 遊戲商回傳所有遊戲投注紀錄筆數
    
    echo "<table border='1'>
            <tr>
            <th>遊戲種類</th>
            <th>遊戲廠商</th>
            <th>帳號</th>
            <th>下注時間</th>
            <th>結算時間</th>
            <th>投注編號</th>
            <th>投注額</th>
            <th>有效投注額</th>
            <th>輸贏</th>
            <th>遊戲押注</th>
            <th>遊戲結果</th>
            <th>遊戲資訊</th>
            </tr>";
    if($obj['items'] != "")
    {

        for($i=0;$i<count($obj['items']);$i++)
        {
            $list = $obj['items'][$i];
    
            $gameT = API_TYPE; //遊戲種類代碼
            $gameF = API_GAMENAME; //遊戲平台
            
            $mid = diset_account($list['player_id']); // 會員帳號
            $gTime = date('Y-m-d H:i:s',strtotime($list['c_at']));//起始時間(投注時間)
            $glTime = date('Y-m-d H:i:s',strtotime($list['c_at']));//歸帳日(結算/派彩時間)
            $gid = $list['transaction_id']; //投注編號(唯一值)
            $gBet = $list['bet']; //投注總額
            $gBetR = $list['bet']; //有效投注額
            $gWL = $list['win'] - $gBetR; //輸贏
            $gPs = $list['round_id'];//下注資料 遊戲押注
            if($gWL > 0){
                $gResut = 'win';
            }elseif($gWL == 0){
                $gResut = 'tie';
            }elseif($gWL < 0){
                $gResut = 'lose';
            }
            $gInfo = $list['game_id']."[@]".$list['game_name']; //遊戲代碼 遊戲資訊
            echo "<tr>";
            echo "<td>" . $gameT . "</td>";
            echo "<td>" . $gameF . "</td>";
            echo "<td>" . $mid . "</td>";
            echo "<td>" . $gTime . "</td>";
            echo "<td>" . $glTime . "</td>";
            echo "<td>" . $gid . "</td>";
            echo "<td>" . $gBet . "</td>";
            echo "<td>" . $gBetR . "</td>";
            echo "<td>" . $gWL . "</td>";
            echo "<td>" . $gPs . "</td>";
            echo "<td>" . $gResut . "</td>";
            echo "<td>" . $gInfo . "</td>";
            echo "</tr>";
            
            // echo '測試:'.$gid.' '.$gTime.' '.$glTime.' '.$mid.' '.$gBet.' '.$gBetR.' '.$gWL.' '.$gResut.'<br>';
            if(!(in_array($gid,$all_gid)))
            {              
                echo '新增:'.$gid.' '.$gTime.' '.$glTime.' '.$mid.' '.$gBet.' '.$gBetR.' '.$gWL.' '.$gResut.'<br>';
                // $AddDB = SetAddDB($AddDB,'sid','',0); //系統ID
                // $AddDB = SetAddDB($AddDB,'mid',$mid,1); //帳號ID
                // $AddDB = SetAddDB($AddDB,'gTime',$gTime,1); //起始時間
                // $AddDB = SetAddDB($AddDB,'glTime',$glTime,1); //歸帳日
                // $AddDB = SetAddDB($AddDB,'gameT',$gameT,1); //遊戲類型
                // $AddDB = SetAddDB($AddDB,'gameF',$gameF,1); //遊戲平台
                // $AddDB = SetAddDB($AddDB,'gBet',$gBet,1); //投注額
                // $AddDB = SetAddDB($AddDB,'gBetR',$gBetR,1); //有效投注額
                // $AddDB = SetAddDB($AddDB,'gWL',$gWL,1); //輸贏結果
                // $AddDB = SetAddDB($AddDB,'gid',$gid,1); //遊戲投注ID
                // $AddDB = SetAddDB($AddDB,'gInfo',$gInfo,1); //遊戲資訊
                // $AddDB = SetAddDB($AddDB,'gPS',$gPs,1); //遊戲押注
                // $AddDB = SetAddDB($AddDB,'gResut',$gResut,1); //遊戲成績
                // $AddDB = SetAddDB($AddDB,'lastUpdateMan','API',1); //最後異動人員
                // $AddDB = SetAddDB($AddDB,'lastUpdateTime',date('Y-m-d H:i:s'),1); //最後異動時間
                // $AddDB = SetAddDB($AddDB,'ip',$ip,1); //最後異動IP
                // $addMsg = AddDB('gameRecord',$AddDB[0],$AddDB[1],'0');
    
                // $AddDB = SetAddDB($AddDB,'sid','',0); //系統ID
                // $AddDB = SetAddDB($AddDB,'mid',$mid,1); //帳號ID
                // $AddDB = SetAddDB($AddDB,'gTime',$gTime,1); //起始時間
                // $AddDB = SetAddDB($AddDB,'glTime',$glTime,1); //歸帳日
                // $AddDB = SetAddDB($AddDB,'gameT',$gameT,1); //遊戲類型
                // $AddDB = SetAddDB($AddDB,'gameF',$gameF,1); //遊戲平台
                // $AddDB = SetAddDB($AddDB,'gBet',$gBet,1); //投注額
                // $AddDB = SetAddDB($AddDB,'gBetR',$gBetR,1); //有效投注額
                // $AddDB = SetAddDB($AddDB,'gWL',$gWL,1); //輸贏結果
                // $AddDB = SetAddDB($AddDB,'gid',$gid,1); //遊戲投注ID
                // $AddDB = SetAddDB($AddDB,'gInfo',$gInfo,1); //遊戲資訊
                // $AddDB = SetAddDB($AddDB,'gPS',$gPs,1); //遊戲押注
                // $AddDB = SetAddDB($AddDB,'gResut',$gResut,1); //遊戲成績
                // $AddDB = SetAddDB($AddDB,'lastUpdateMan','API',1); //最後異動人員
                // $AddDB = SetAddDB($AddDB,'lastUpdateTime',date('Y-m-d H:i:s'),1); //最後異動時間
                // $AddDB = SetAddDB($AddDB,'ip',$ip,1); //最後異動IP
                // $addMsg = AddDB('gameRecord_tmp',$AddDB[0],$AddDB[1],'0');
            }
    
            
        }
    }
    else
    {
        $msg = array(
            'code' => '18',
            'msg' => 'Gamerecord failed on '.date('Y-m-d H:i:s'),
            'type' => 'record',
            'game' => API_CODENAME,
            'gameMsg' =>  $obj['Message'], // 遊戲商回傳errorMsg/errorCode
        );
        print_o(json_encode($msg,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

}


function csBalance($mid)
{
    $sql = "SELECT money FROM NmemMoney WHERE mid = '".diset_account($mid)."'";
    $res = mysql_query($sql) or die("資料查詢失敗,請聯絡管理員,".mysql_error());
    $num = mysql_num_rows($res);
    $row = mysql_fetch_array($res);
    $amount = $row['money'];
    return $amount;
}

function post_curl($url, $params)
{
    $headers = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($params))
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);      

    $output = curl_exec($ch);

    $output = json_decode($output, true);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // echo "<br/>http_code: " . $http_code.'<br/>';
    curl_close($ch);
    // print_o($output);
    return $output;
}

?>