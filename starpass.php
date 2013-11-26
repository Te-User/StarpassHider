<?php
    class Payment extends Starpass
    {
        public $SMS = Array();
        public $Audiotel = Array();
    }
    class Starpass
    {
        public $IDP     = 123456;        // ID de votre compte STARPASS.
        public $IDD     = 123456;       // ID de votre document STARPASS.
        public $URL_D   = 'ok.php';     // URL vers la page protéger.
        public $URL_E   = 'error.php';  // URL vers la page d'erreur.
         
        /* 
            Configuration avancée.
        */
         
        public $PREPUT  = 'mKazl';      // Préfixe du champs des formulaires.
        public $SUBPUT  = 'pAslk';      // Sufixe du champs des formulaires.
        public $PaysAuthorized = Array('fr', 'es', 'pt', 'ch');
        public $PaysPayment = Array();
         
        private function CodeError  ()
        {
            $this->error(2);
        }
         
        private function CodeValide ()
        {
            /*
                Veuillez éxecuter ici le code PHP lorsque le code est valide !
            */
        }
         
        public function Protect() 
        {
            if(isset($_POST[$this->PREPUT . '1' . $this->SUBPUT])) 
            {
                $ident=$idp=$ids=$idd=$codes=$code1=$code2=$code3=$code4=$code5=$datas=''; 
                $idp = $this->IDP; 
                $idd = $this->IDD;
                $ident=$idp.";".$ids.";".$idd;
                if(isset($_POST[$this->PREPUT . '1' . $this->SUBPUT])) $code1 = $_POST[$this->PREPUT . '1' . $this->SUBPUT]; 
                if(isset($_POST[$this->PREPUT . '2' . $this->SUBPUT])) $code2 = ";".$_POST[$this->PREPUT . '2' . $this->SUBPUT]; 
                if(isset($_POST[$this->PREPUT . '3' . $this->SUBPUT])) $code3 = ";".$_POST[$this->PREPUT . '3' . $this->SUBPUT]; 
                if(isset($_POST[$this->PREPUT . '4' . $this->SUBPUT])) $code4 = ";".$_POST[$this->PREPUT . '4' . $this->SUBPUT]; 
                if(isset($_POST[$this->PREPUT . '5' . $this->SUBPUT])) $code5 = ";".$_POST[$this->PREPUT . '5' . $this->SUBPUT]; 
                $codes=$code1.$code2.$code3.$code4.$code5; 
                if(isset($_POST['DATAS'])) $datas = $_POST['DATAS']; 
                $ident=urlencode($ident);
                $codes=urlencode($codes);
                $datas=urlencode($datas);
                $get_f=@file( "http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas" ); 
                if(!$get_f) 
                { 
                    $this->error(1);
                } 
                $tab = explode("|",$get_f[0]);
 
                if(!$tab[1]) $url = $this->URL_E; 
                else $url = $tab[1]; 
                $pays = $tab[2]; 
                $palier = urldecode($tab[3]); 
                $id_palier = urldecode($tab[4]); 
                $type = urldecode($tab[5]); 
                if( substr($tab[0],0,3) != "OUI" ) 
                { 
                    $this->CodeError();
                    echo "http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas";
                } 
                else
                { 
                    $this->CodeValide();
                } 
            }
            else
            {
                $this->error(0);
            }
        }
        public function ShowForm($NB_CODE)
        {
            $SHOW_FOR = $this->PaysAuthorized[0];
            $SHOW_TYPE = 0;
            $active = Array('active', '');
            if(isset($_GET['pat'])) {
                $SHOW_FOR = htmlentities($_GET['pat']);
            }
            if(isset($_GET['set'])) {
                $SHOW_TYPE = intval($_GET['set']);
            }
            if($SHOW_TYPE==0) {
                $active = Array('active', '');
            } else {
                $active = Array('', 'active');
            }
     
            $SRC_SCRIPT = file_get_contents('http://script.starpass.fr/script.php?idd='.$this->IDD.'&amp;verif_en_php=1&amp;datas=');
            $this->parsingScript($SRC_SCRIPT);
            echo ('<div id="CallContent">
                        <span id="onglet'.$active[0].'"><a href="?set=0&pat='.$SHOW_FOR.'">Audiotel</a></span>
                        <span id="onglet'.$active[1].'"><a href="?set=1&pat='.$SHOW_FOR.'">SMS</a></span>
                        <div id="content">');
            printf ('<form method="POST" action="%s">', $this->URL_D);
             
             
             
            for ($i = 0; $i < count($this->PaysAuthorized); $i++)
            {
                $Pays_IF = $this->PaysPayment;
         
                if($this->PaysAuthorized[$i] == $SHOW_FOR) {
                    printf('<img id="actuPay" src="flag/%s.png" />', $this->PaysAuthorized[$i]);
                    switch($SHOW_TYPE) 
                    {
                        default:
                            break;
                        case 0:
                            if($Pays_IF[$i]->Audiotel[0] != null) {
                                // Affichage des informations pour le paiement audiotel.
                                echo ('Téléphonez au <b>' . $Pays_IF[$i]->Audiotel[0] . '</b>
                                          <i>' . $Pays_IF[$i]->Audiotel[1] . ' TTC/appel ' . $Pays_IF[$i]->Audiotel[2] . ' TTC/minute - Seul le coût de la communication vous sera facturé.</i>
                                ');
                            } else {
                                echo 'Ce pays ne propose pas de paiement par Audiotel.';
                            }
                            break;
                        case 1:
                            if($Pays_IF[$i]->SMS[0] != null) {
                                // Affichage des informations pour le paiement SMS.
                                if(substr_count($Pays_IF[$i]->SMS[0], "|") == 0) {
                                        echo ('Envoyer <b>'.$Pays_IF[$i]->SMS[1].'</b> par SMS au <b>' . $Pays_IF[$i]->SMS[0] . '</b>
                                          <i>1 x '.$Pays_IF[$i]->SMS[2].' + coût d\'un SMS</i>
                                    ');
                                } else {
                                    echo 'Le script rend l\'utilisation des SMS impossible car il y a plusieurs numéros.';
                                }
 
                            } else {
                                echo 'Ce pays ne propose pas de paiement par SMS.';
                            }
                            break;
                    }
                }
                     
            }
            echo '';
            for ($i = 1; $i < $NB_CODE + 1; $i++) 
            {
                printf ('<input placeholder="Code %s" type="search" name="%s%s%s" size="8" />', $i, $this->PREPUT, $i, $this->SUBPUT);
            }
            printf ('<input type="submit" id="send" value="Valider mes codes" />');
             
            /*
                Affichage des drapeaux.
            */
            for ($i = 0; $i < count($this->PaysAuthorized); $i++){
                printf('<a href="?set='.$SHOW_TYPE.'&pat=%s"><img src="flag/%s.png"></a> ', $this->PaysAuthorized[$i], $this->PaysAuthorized[$i]);
            }
            printf ('</form></div></div>');
        }
         
        private function parsingScript($src)
        {
             
            $src = explode('oSmsAudiotelDataDoc'.$this->IDD.' = {', $src);
            // Boucle des pays
            for($i = 0; $i < count($this->PaysAuthorized); $i++)
            {
                $Payment = new Payment();
                 
                // Parse le code du PAYS.
                $getJSFrom = explode('"'.$this->PaysAuthorized[$i].'"', $src[1]);
                $getJSFrom = explode(',"iMultiStatus":0}},', $getJSFrom[1]);
                $getJSFrom = $getJSFrom[0] . 'iMultiStatus';
                 
                // Récupération des informations de paiement par Audiotel.
                if(substr_count($getJSFrom, "audiotel") != 0) 
                {
                    $getAUDIOTEL = explode('"audiotel":', $getJSFrom);
                    $getAUDIOTEL = explode('iMultiStatus', $getAUDIOTEL[1]);
                     
                    $Payment->Audiotel[0] = $this->getValue('audiotelPhone', $getAUDIOTEL[0]);
                    $Payment->Audiotel[1] = $this->getValue('audiotelFixedCostDetail', $getAUDIOTEL[0]);
                    $Payment->Audiotel[2] = $this->getValue('audiotelVariableCostDetail', $getAUDIOTEL[0]);
                } else {
                    $Payment->Audiotel[0] = null;
                    $Payment->Audiotel[1] = null;
                    $Payment->Audiotel[2] = null;
                }
                 
                // Récupération des informations de paiement par SMS.
                if(substr_count($getJSFrom, "sms") != 0) 
                {
                    // Parse le code SMS.
                    $getSMS = explode('"sms":', $getJSFrom);
                    $getSMS = explode('iMultiStatus', $getSMS[1]);
                    if(substr_count($getSMS[0], 'Keyword":') == 1)
                    {
                        $Payment->SMS[0] = $this->getValue('sContactBookPhone', $getSMS[0]);
                        $Payment->SMS[1] = $this->getValue('smsKeyword', $getSMS[0]);
                        $Payment->SMS[2] = $this->getValue('smsCostDetail', $getSMS[0]);
                    } else {
                        $decSt = explode('},', $getSMS[0]);
                        $lastNumSMS = '';
                        $lastPriceSMS = '';
                        $SMS_KEY_LIST = '';
                        $SMS_NUM_LIST = '';
                        $SMS_PRI_LIST = '';
                        for($i2 = 0; $i2 < substr_count($getSMS[0], 'Keyword":') - 1; $i2++)
                        {
                            // Récupération du numéro de téléphone.
                            $Phone = explode("Phone", $decSt[$i2]);
                            if(isset($Phone[1])) 
                            {
                                $Phone = explode('"', $Phone[1]);
                                $Phone = explode('"', $Phone[2]);
                                $lastNumSMS = $Phone[0];
                            }
                            else {
                                $Phone[0] = $lastNumSMS;
                            }
                            $SMS_NUM_LIST .= $Phone[0] . "|";
 
                            // Récupération de la clé.
                            $keyWord = explode('Keyword":"', $decSt[$i2] );
                            $keyWord = explode('"', $keyWord[1]);
                            $SMS_KEY_LIST .= $keyWord[0] . "|";
                             
                             
                            $Payment->SMS[0] = $SMS_NUM_LIST;
                            $Payment->SMS[1] = $SMS_KEY_LIST;
                            $Payment->SMS[2] = $this->getValue('smsCostDetail', $getSMS[0]);
 
                        }
                    }
                     
                } else {
                    $Payment->SMS[0] = null;
                    $Payment->SMS[1] = null;
                    $Payment->SMS[2] = null;
                }
                array_push($this->PaysPayment, $Payment);
            }
        }
         
        private function getValue($value, $at)
        {
            $dec = explode('"'.$value.'"', $at);
            $dec2 = explode(':"', $dec[1]);
            $dec3 = explode('"', $dec2[1]);
            return $dec3[0];
        }
     
        private function error ($eid) 
        {
            $ErrorList = Array(
                "Il n'y a aucune requête.",
                "Le serveur ne parvient pas a se connecter au service de micropaiement...",
                "Le ou les code(s) ne sont pas valide..."
            );
            printf($ErrorList[$eid]);
        }
        private function gName ($id)
        {
            if($id==0) 
            {
                return "Audiotel";
            } else {
                return "SMS";
            }
        }
    }
?>
