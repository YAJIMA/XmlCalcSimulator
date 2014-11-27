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

/**
 * フォームから来たデータをエンコードする
 * @param array $post フォームから来たデータ
 */
function FormEncode(&$post){
	if(!isset($post['enc'])){
		return;
	}
	//どのエンコーディングか判定
	$enc = mb_detect_encoding($post['enc']);
	$default_enc = "UTF-8";
	foreach($post as &$value) {
		EncodeCore($value,$default_enc,$enc);
	}
	unset($value);
}
/**
* エンコードのコア部分
* @param unknown_type $value
* @param string $default_enc
* @param string $enc
*/
function EncodeCore(&$value, $default_enc, $enc){
	if(is_array($value)){
		//配列の場合は再帰処理
		foreach ($value as &$value2) {
			EncodeCore($value2, $default_enc, $enc);    
		}
	}elseif($enc != $default_enc){
		//文字コード変換
		$value = mb_convert_encoding($value, $default_enc, $enc) ;
	}
}

/**
* メール送信関数
*/
function HBsendMail($to, $subject, $body, $from_email, $from_name, $from_enc="UTF-8", $files){
	mb_language("ja");
	mb_internal_encoding($from_enc);
	$result = false;
	
	/* Mail, headers */
	$headers  = "MIME-Version: 1.0 \n" ;
	
	/* Mail, body */
	$body = mb_convert_encoding($body, "ISO-2022-JP", $from_enc);
	
	/* Mail, optional paramiters. */
	$sendmail_params  = "-f$from_email";
	
	/* Mail, subject */
	$subject = mb_convert_encoding($subject, "ISO-2022-JP", $from_enc);
	$subject = "=?iso-2022-jp?B?" . base64_encode($subject) . "?=";
	
	/* Ataachment */
	if(isset($files) && is_array($files)){
		// boundary
		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		// headers for attachment
		$headers .= "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"\n";
		// multipart boundary
		$body = "--{$mime_boundary}\n" . "Content-Type: text/plain;charset=\"ISO-2022-JP\"\n" .
		"Content-Transfer-Encoding: 7bit\n\n" . $body . "\n\n";
		// preparing attachments
		for($i=0;$i<count($files);$i++){
			if(is_file($files[$i])){
				$body .= "--{$mime_boundary}\n";
				$fp = @fopen($files[$i],"rb");
				$data = @fread($fp,filesize($files[$i]));
				@fclose($fp);
				$data = chunk_split(base64_encode($data));
				$body .= "Content-Type: application/octet-stream; name=\"".basename($files[$i])."\"\n" . 
				"Content-Description: ".basename($files[$i])."\n" .
				"Content-Disposition: attachment;\n" . " filename=\"".basename($files[$i])."\"; size=".filesize($files[$i]).";\n" . 
				"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
			}
		}
		$body .= "--{$mime_boundary}--";
	}else{
		$headers .= "Content-Type: text/plain;charset=ISO-2022-JP\n";
	}
	
	/* Additional Header */
	$headers .= "From: " .
		mb_encode_mimeheader (mb_convert_encoding($from_name,"ISO-2022-JP",$from_enc)) .
		"<".$from_email.">\n";
	$headers .= "Reply-To: " .
		mb_encode_mimeheader (mb_convert_encoding($from_name,"ISO-2022-JP",$from_enc)) .
		"<".$from_email.">\n";
	
	/* Mail, sending */
	$result = mail($to, $subject, $body, $headers, $sendmail_params);
	
	return $result;
}
?>