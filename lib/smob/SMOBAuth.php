<?php

/* 
    Helper methods for the authentication
    FOAF-SSL methods from : https://foaf.me/testLibAuthentication.php    
*/


class SMOBAuth {

    function authorize() {
    //FIXME: hackish
        error_log("auth:authorize",0);
        session_start();
        //'subjectAltName'
        if (isset($_SESSION['isOwner']) || isset($_SESSION['isHub'])) {
            error_log("Auth::authorize, owner already set",0);
            return ($_SESSION['isOwner'] || $_SESSION['isHub']);
        } else {
            error_log("Auth::authorize, owner not set",0);
            return false;
        }
    }
    
    function check() {
        session_start();
        return $_SESSION['isAuthenticated'];
    }
    
    function grant() {
        error_log("Auth::grant",0);
        session_start();
        //if(AUTH == 'foafssl') {
        if (isset($_SESSION['isAuthenticated'])) {
            error_log("Auth::grant, already set",0);
            return $_SESSION['isAuthenticated'];
        }elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  &&  isset($_SERVER['SSL_CLIENT_CERT']) && $_SERVER['SSL_CLIENT_CERT']) {
            // TODO: instead of checking a config variable, we should just check if a
            // certificate is provided 
            // TODO, the WebID URI in the certificate should also match the owner 
            // WebID URI for site access or the hub WebID for private profile access
            error_log('fAuth::grant, foafssl authentication',0);
            $foafssl  = SMOBAuth::getAuth();
            $_SESSION['isAuthenticated'] = $foafssl['isAuthenticated'];
            $_SESSION['isOwner'] = $foafssl['isOwner'];
            $_SESSION['isHub'] = $foafssl['isHub'];
            error_log($foafssl['authDiagnostic'],0);
           return $_SESSION['isAuthenticated'];
        } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
            echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
            echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
            $_SESSION['isAuthenticated'] = true;
            return $_SESSION['isAuthenticated'];
            // fixme: nothing else checked if not foafssl?, what about http authentication?
        } elseif (!isset($_SERVER['PHP_AUTH_USER'])  && AUTH == 'basichttp') {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Text to send if user hits Cancel button';
            exit;
        } else {
            // go with sempush authentication...
            // or deny access
            error_log("Auth::grant, not authenticated",0);
            //$_SESSION['isAuthenticated'] = false;
            //return $_SESSION['isAuthenticated'];
            return false;
        }
    }
    
    /* Function to return the modulus and exponent of the supplied Client SSL Page */
    function openssl_pkey_get_public_hex()
    {
        if ($_SERVER['SSL_CLIENT_CERT'])
        {
          error_log('got a certificate',0);
            $pub_key = openssl_pkey_get_public($_SERVER['SSL_CLIENT_CERT']);
            $key_data = openssl_pkey_get_details($pub_key);

            $key_len   = strlen($key_data['key']);
            $begin_len = strlen('-----BEGIN PUBLIC KEY----- ');
            $end_len   = strlen(' -----END PUBLIC KEY----- ');

            $rsa_cert = substr($key_data['key'], $begin_len, $key_len - $begin_len - $end_len);

            $rsa_cert_struct = `echo "$rsa_cert" | openssl asn1parse -inform PEM -i`;

            $rsa_cert_fields = split("\n", $rsa_cert_struct);
            $rsakey_offset   = split(":",  $rsa_cert_fields[4]);

            $rsa_key = `echo "$rsa_cert" | openssl asn1parse -inform PEM -i -strparse $rsakey_offset[0]`;

            $rsa_keys = split("\n", $rsa_key);
            $modulus  = split(":", $rsa_keys[1]);
            $exponent = split(":", $rsa_keys[2]);

            return( array( 'modulus'=>$modulus[3], 'exponent'=>$exponent[3] ) );
        }
        error_log('no certificate',0);
    }

    /* Returns an array holding the subjectAltName of the supplied SSL Client Certificate */
    function openssl_get_subjectAltName()
    {
        if ($_SERVER['SSL_CLIENT_CERT'])
        {
            $cert = openssl_x509_parse($_SERVER['SSL_CLIENT_CERT']);
            if ($cert['extensions']['subjectAltName'])
            {
                $list = split("[,]", $cert['extensions']['subjectAltName']);

                for ($i = 0, $i_max = count($list); $i < $i_max; $i++) 
                {
                    if (strcasecmp($list[$i],"")!=0)
                    {
                        $value = split(":", $list[$i], 2);
                        if (isset($subject_array))
                            $subject_array = array_merge($subject_array, array(trim($value[0]) => trim($value[1])));
                        else
                            $subject_array = array(trim($value[0]) => trim($value[1]));
                    }
                }

                return $subject_array;
            }
        }
    }

    /* Function to clean up teh supplied hex and convert numbers A-F to uppercase characters eg. a:f => AF */
    function cleanhex($hex)
    {
        $hex = eregi_replace("[^a-fA-F0-9]", "", $hex); 
        $hex = strtoupper($hex);
        return($hex);
    }

    /* Returns an array of the modulus and exponent in the supplied RDF */
    function get_foaf_rsakey($uri)
    {
        error_log("Auth::get_foaf_rsakey",0);
        if ($uri)
        {
            error_log("in if uri:",0);
            error_log($uri,0);
            // while not support for several certificates/keys, delete the existing 
            // ones before insert another to ensure there's only one
            $res = SMOBStore::query('DELETE FROM <'.$uri.'>');
            error_log('SMOBAuth::get_foaf_rsakey result delete:',0);
            error_log(print_r($res,1),0);
            $res = SMOBStore::query('LOAD <'.$uri.'>');
            error_log('SMOBAuth::get_foaf_rsakey result load:',0);
            error_log(print_r($res,1),0);
            //$result = SMOBTools::do_curl($uri, null, null, $type='GET');

            /* list names */
            
            $q = '
              SELECT ?mod ?exp  WHERE {
                [] a rsa:RSAPublicKey;
                    cert:identity <'.$uri.'>;
                    rsa:modulus  [ cert:hex ?mod ] ;
                    rsa:public_exponent  [ cert:decimal ?exp ] .
              }';
            $q = '
              SELECT DISTINCT ?mod ?exp  WHERE {
                 [] cert:identity <'.$uri.'> ;
                    a rsa:RSAPublicKey ;
                    rsa:modulus ?mod ;
                    rsa:public_exponent ?exp.
              }';

            $res = SMOBStore::query($q);
            error_log('SMOBAuth::get_foaf_rsakey result select:',0);
            error_log(print_r($res,1),0);
            if ($res) {
                // TODO: support several keys for webid uri?
                $modulus =  SMOBAuth::cleanhex($res[0]['mod']);
                $exponent =  SMOBAuth::cleanhex($res[0]['exp']);
                error_log('modulus: ',0);
                error_log($modulus, 0);
                error_log('exponent: ',0);
                error_log($exponent, 0);
            }
            if ($modulus && $exponent)
                return (array( 'modulus'=>$modulus, 'exponent'=>dechex($exponent) ) );
        }
        error_log("Auth::get_foaf_rsakey no uri parameter",0);
    }

    /* Function to compare two supplied RSA keys */
    function equal_rsa_keys($key1, $key2)
    {
        if ( $key1 && $key2 && ($key1['modulus'] == $key2['modulus']) && ($key1['exponent'] == $key2['exponent']) )
            return TRUE;

        return FALSE;
    }

    function getAuth($foafuri = NULL)
    {
        if (!$_SERVER['HTTPS'])
            return ( array( 'isAuthenticated'=>0 , 'authDiagnostic'=>'No client certificate supplied on an unsecure connection') );

        if (!$_SERVER['SSL_CLIENT_CERT'])
            return ( array( 'isAuthenticated'=>0 , 'authDiagnostic'=>'No client certificate supplied') );

        //FIXME: when the request comes from sempush, there'll not be HTTPS/SSL_CLIENT_CERT, so we have to perform authentication with other parameter/header

        error_log('certificate:',0);
        error_log($_SERVER['SSL_CLIENT_CERT'], 0);
        $certrsakey = SMOBAuth::openssl_pkey_get_public_hex();

        if (!$certrsakey)
            return ( array( 'isAuthenticated'=>0 , 'authDiagnostic'=>'No RSA Key in the supplied client certificate') );
        error_log('certificate rsa key:',0);
        error_log($certrsakey['modulus'],0);

        $result = array('certRSAKey'=>$certrsakey);

        $san     = SMOBAuth::openssl_get_subjectAltName();
        $foafuri = $san['URI'];
        error_log('Auth::getAuth foaf uri:',0);
        error_log($foafuri,0);
    
        $result = array_merge($result, array('subjectAltName'=>$foafuri));

        $foafrsakey = SMOBAuth::get_foaf_rsakey($foafuri);
        //error_log('foaf rsa key:',0);
        //error_log($foafrsakey['modulus'],0);
        
        $result = array_merge($result, array('subjectAltNameRSAKey'=>$foafrsakey));

        if ( SMOBAuth::equal_rsa_keys($certrsakey, $foafrsakey) )
            $result = array_merge($result, array( 'isAuthenticated'=>1,  'authDiagnostic'=>'Client Certificate RSAkey matches SAN RSAkey'));
        else
            $result = array_merge($result, array( 'isAuthenticated'=>0,  'authDiagnostic'=>'Client Certificate RSAkey does not match SAN RSAkey'));

        // Authorization:
        // isOwner?
        //$q = "ASK { <$foafuri> a foaf:Person }"; 
        //$res = SMOBStore::query($q);
        //error_log('SMOBAuth::getAuth, result ask:',0);
        //error_log(print_r($res,1),0);
        //$result = array_merge($result, array( 'isOwner'=>$res[0]));
        //FIXME: previous query doesn't work, compare with foaf uri
        if (FOAF_URI == trim($foafuri,'#me')) {
            $result = array_merge($result, array( 'isOwner'=>1));
        } else {
            $result = array_merge($result, array( 'isOwner'=>0));
        }
        // isHub?
        //$q = "ASK 
        //{ <$foafuri> a push:SemanticHub . 
        //".ME_FEED_URL."  push:has_hub <$foafuri>
        //".ME_FEED_URL."  push:has_owner ".ME_URL."}"; 
        //$res = SMOBStore::query($q);
        //error_log('SMOBAuth::getAuth, result ask:',0);
        //error_log(print_r($res,1),0);
        //$result = array_merge($result, array( 'isHub'=>$res[0]));
        //FIXME: hackish
        if (HUB_URL."/me" == $foafuri) {
            $result = array_merge($result, array( 'isHub'=>1));
        } else {
            $result = array_merge($result, array( 'isHub'=>0));
        }
        // isAuthorized?
        //

        error_log("getAuth result:",0);
        error_log(print_r($result,1),0);
        return $result;
    }

}
