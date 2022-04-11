<?
ini_set("display_errors","1");
ini_set("display_startup_errors","1");
ini_set('error_reporting', E_ALL);
writetolog($_REQUEST, 'new request');	
$deal = $_REQUEST['deal'];
$cnt = $_REQUEST['cnt'];
$user = $_REQUEST['user'];

/* AUTH */
require_once('auth.php');


$dealinfo = executeREST(
            'crm.deal.get',
            array(
                    'ID' => $deal
            ),
            $domain, $auth, $user);

$summ      = $dealinfo['result']['UF_CRM_1612278529'];
//writetolog($dealinfo, 'dealinfo');

	// добавляем счет
	$contoadd = executeREST(
            'crm.invoice.add',
            array(
                        'FIELDS' => array(   
								'ORDER_TOPIC' => 'Оплата рассрочки',
								'STATUS_ID' => 'N',
								'UF_QUOTE_ID' => 0,
							    'UF_DEAL_ID' => $deal,
								'UF_COMPANY_ID' => 0,
								'UF_CONTACT_ID' => $cnt,
								'UF_MYCOMPANY_ID' => 58,
								'PAYED' => 'N',
								'PAY_SYSTEM_ID' => 4,
								'PERSON_TYPE_ID' => 8,
                                'RESPONSIBLE_ID' => $user,
                                'INVOICE_PROPERTIES' => array (
									'FIO' => 'testct'
																		
								),
								'PRODUCT_ROWS' => array( 
									array (  
										'ID' => 0,
										'PRODUCT_ID' => 207,
										'PRODUCT_NAME' => 'Оплата рассрочки',
										'QUANTITY' => 1,
										'PRICE' => $summ,
									),
								),
                            ),
                    ),
		$domain, $auth, $user);

		$contoadd2 = $contoadd['result'];

        $link = executeREST(
            'crm.invoice.getexternallink',
            array(
                'ID' => $contoadd,
            ),
        $domain, $auth, $user);

        $link2 = $link['result'];
        $updatedeal = executeREST(
            'crm.deal.update',
            array(
					'ID' => $deal,	
					'FIELDS' => array (
						'UF_CRM_1612517885' => $link2,
						'UF_CRM_1612789625' => $contoadd2,
						),
					'PARAMS' => array (
						'REGISTER_SONET_EVENT' => "N",
						),
                    ),
	$domain, $auth, $user);


function executeREST ($method, array $params, $domain, $auth, $user) {
            $queryUrl = 'https://'.$domain.'/rest/'.$user.'/'.$auth.'/'.$method.'.json';
            $queryData = http_build_query($params);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POST => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $queryUrl,
                CURLOPT_POSTFIELDS => $queryData,
            ));
            return json_decode(curl_exec($curl), true);
            curl_close($curl);
}

function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/generateconto.log', $log, FILE_APPEND);
    return true;
} 