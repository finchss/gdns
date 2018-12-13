<?php
	function next_iteration($str, $charset) {
		/*
			stackoverflow code ripping.
		*/
		// last character in charset that requires a carry-over
		$copos = strlen($charset)-1;
		// starting with the least significant digit
		$i = strlen($str)-1;
		do {
			// reset carry-over flag
			$co = false;
			// find position of digit in charset
			$pos = strpos($charset, $str[$i]);
			if ($pos === false) {
				// invalid input char at position $i
				return false;
			}
			// check whether it’s the last character in the charset
			if ($pos === $copos) {
				// we need a carry-over to the next higher digit
				$co = true;
				// check whether we’ve already reached the highest digit
				if ($i === 0) {
					// no next iteration possible due to fixed string length
					return false;
				}
				// set current digit to lowest charset digit
				$str[$i] = $charset[0];
			} else {
				// if no carry-over is required, simply use the next higher digit
				// from the charset
				$str[$i] = $charset[$pos+1];
			}
			// repeat for each digit until there is no carry-over
			$i--;
		} while ($co);
		return $str;
	}
	function DnsBruteForce($Domain,$StartStr,$Chars){
		echo "\n";
		do {
			echo "\r$StartStr.$Domain";flush();
			$res=GooglerResolve($StartStr.".".$Domain);
			if($res!=false){
				$out="";
				foreach($res as $k=>$v){
					$out.=$v['data'].",";
				}
				$out=trim($out,",");
				$fp=fopen("df-$Domain.txt","a+");
				fputs($fp,$StartStr.".".$Domain.":".$out."\n");
				echo "\r".$StartStr.".".$Domain.":".$out."\n";
				fclose($fp);
			}
		} while (($StartStr = next_iteration($StartStr, $Chars)) !== false);	
	}
	function GooglerResolve($Host){

		//echo $Host."\n";
		global $vGoogleResolverCh;

		$retry=0;
		start: 
		if(gettype($vGoogleResolverCh)!="resource") 
			$vGoogleResolverCh=curl_init("https://dns.google.com/resolve?name=$Host");
		else
			curl_setopt($vGoogleResolverCh,CURLOPT_URL,"https://dns.google.com/resolve?name=$Host");

		curl_setopt($vGoogleResolverCh,CURLOPT_VERBOSE,0);
		curl_setopt($vGoogleResolverCh,CURLOPT_SSL_VERIFYPEER , 0);
		curl_setopt($vGoogleResolverCh,CURLOPT_RETURNTRANSFER,1);
		$d=json_decode(curl_exec($vGoogleResolverCh),TRUE);
		$x=curl_getinfo($vGoogleResolverCh);

		if($x['http_code']!=200) {
			echo "http code was ".$x['http_code'].", retrying \n";
			sleep(1);
			goto start;
		}

		if (($d['Status']!=0) && ($d['Status']!=3) && ($retry<5)){
			$retry++;
			echo "$Host: dns status is ".$d['Status'].", retrying in 1 second\n";
			sleep(1);
			goto start;
		}

		if(isset($d['Answer'])) 
			return $d['Answer']; 
		else 
			return false;
	}
	DnsBruteForce($argv[1],"a","abcdefghijklmnoprtuvxywz");
	DnsBruteForce($argv[1],"aa","abcdefghijklmnoprtuvxywz");
	DnsBruteForce($argv[1],"aaa","abcdefghijklmnoprtuvxywz");
	DnsBruteForce($argv[1],"aaaaa","abcdefghijklmnoprtuvxywz");

?>
