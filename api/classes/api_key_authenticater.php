<?php


class API_KEY_AUTHENTICATER
{
    public static function api_auth($DATA)
    {   
        global $db;
        if ($db->error_number) die("Connection Error");
        $flag = 1;
        $API_KEY = '';
        foreach (getallheaders() as $name => $value) {
            //echo "$name: $value<br />";
            if (strtolower(trim($name)) == 'apikey')
                $API_KEY = trim($value);
        }

        if ($API_KEY == '') {
            $data['SUCCESS'] = 0;
            $data['ERROR'] = ['API Key Missing'];

            $flag = 0;
        } else {
            $res = $db->Execute("SELECT PK_ACCOUNT,ACTIVE FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
            if ($res->RecordCount() == 0) {
                $data['SUCCESS'] = 0;
                $data['ERROR'] = ['Invalid API Key'];

                $flag = 0;
            } else if ($res->fields['ACTIVE'] == 0) {
                $data['SUCCESS'] = 0;
                $data['ERROR'] = ['Your Account Is Blocked.'];

                $flag = 0;
            }

            $PK_ACCOUNT = $res->fields['PK_ACCOUNT'];
        }
        if ($flag == 0) {
            $data = json_encode($data);
            echo $data;
            exit;
        } else {
            return $PK_ACCOUNT;
        }
    }
}
