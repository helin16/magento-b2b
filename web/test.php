<?php

require_once 'bootstrap.php';

echo '<pre>';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

// convert in PDF
    try
    {
    	
        $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8');
//      $html2pdf->setModeDebug();
        $html = ComScriptCURL::readUrl('http://localhost:8081/', null, array(), '', array(CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_COOKIE => session_name() . '=' . md5(session_id()),
				CURLOPT_COOKIEJAR => session_name() . '=' . md5(session_id())
		));
        $dom = new simple_html_dom();
        $dom->load($html);
        foreach($dom->find('script') as $script)
        	$script->outertext = '';
        $dom->load($dom->save());
//         echo '<textarea>' . $dom->save() . '</textarea>';die;
        echo $dom->save();die;
        $html2pdf->writeHTML('<page>' . $dom->save() . '</page>', false);
        ob_end_clean(); //add this line here
        $html2pdf->Output('exemple00.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
echo 'DONE';