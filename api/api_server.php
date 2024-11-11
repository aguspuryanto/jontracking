<?
    if (@$api_access != true) { die; }
    
    // split command and params
	$cmd = urldecode($cmd);
	$cmd = stripslashes($cmd);
	$cmd = str_getcsv($cmd, ",", '"');
    $command = @$cmd[0];
    $command = strtoupper($command);
    
    if ($command == 'GET_OBJECTS_ONLINE')
    {
        $objects_online = 0;
    
        $q = "SELECT * FROM `gs_objects`";
        $r = mysqli_query($ms, $q);
            
        while($row = mysqli_fetch_array($r))
        {            
            $last_connection = $row['dt_server'];
            $dt_now = gmdate("Y-m-d H:i:s");
            
            $dt_difference = strtotime($dt_now) - strtotime($last_connection);
            if($dt_difference < $gsValues['CONNECTION_TIMEOUT'] * 60)
            {
                $objects_online += 1;
            }
        }
            
        echo $objects_online;
        die;
    }
        
    if ($command == 'GET_OBJECTS_TOTAL_ONLINE')
    {
        $objects_total = 0;
        $objects_online = 0;

        $q = "SELECT * FROM `gs_objects`";
        $r = mysqli_query($ms, $q);
        $objects_total = mysqli_num_rows($r);
            
        while($row = mysqli_fetch_array($r))
        {            
            $last_connection = $row['dt_server'];
            $dt_now = gmdate("Y-m-d H:i:s");
            
            $dt_difference = strtotime($dt_now) - strtotime($last_connection);
            if($dt_difference < $gsValues['CONNECTION_TIMEOUT'] * 60)
            {
                $objects_online += 1;
            }
        }
            
        echo $objects_total.','.$objects_online;
        die;
    }
        
    if ($command == 'CHECK_USER_EXISTS')
    {
        // command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);        
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        if(checkEmailExists($email))
        {
            echo 'true';
        }
        else
        {
            echo 'false';
        }
        die;
    }
        
    if ($command == 'GET_USERS')
    {
        // command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        $result = array();
        
        $q = "SELECT * FROM `gs_users` ORDER BY id ASC";
        $r = mysqli_query($ms, $q);                
        
        while($row = mysqli_fetch_array($r))
        {
            $expires_on = '';
            
            if ($row['account_expire'] == 'true')
            {
                if (strtotime($row['account_expire_dt']) > 0)
                {
                    $expires_on = $row['account_expire_dt'];
                }
            }
            
            $row['privileges'] = json_decode($row['privileges'],true);				
			$privileges = $row['privileges']['type'];
            
            $info = json_decode($row['info'], true);
            
            $result[] = array('username' => $row['username'],
                              'email' => $row['email'],
                              'active' => $row['active'],
                              'expires_on' => $expires_on,
                              'privileges' => $privileges,
                              'api' => $row['api'],
                              'api_key' => $row['api_key'],
                              'dt_reg' => $row['dt_reg'],
                              'dt_login' => $row['dt_login'],
                              'ip' => $row['ip'],
                              'info' => $info);
        }
        
        header('Content-type: application/json');
        echo json_encode($result);
        die;
    }
    
    if ($command == 'GET_USERS_OBJECTS')
    {
        // command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        $result = array();
        
        $q = "SELECT * FROM `gs_users` ORDER BY id ASC";
        $r = mysqli_query($ms, $q);                
        
        while($row = mysqli_fetch_array($r))
        {
            $expires_on = '';
            
            if ($row['account_expire'] == 'true')
            {
                if (strtotime($row['account_expire_dt']) > 0)
                {
                    $expires_on = $row['account_expire_dt'];
                }
            }
            
            $row['privileges'] = json_decode($row['privileges'],true);				
			$privileges = $row['privileges']['type'];
            
            $info = json_decode($row['info'], true);
            
            $objects = array();
            
            $q2 = "SELECT gs_objects.*, gs_user_objects.*
				FROM gs_objects
				INNER JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
				WHERE gs_user_objects.user_id='".$row['id']."'";
            $r2 = mysqli_query($ms, $q2);
            
            while($row2 = mysqli_fetch_array($r2))
            {
                $object_expires_on = '';
                
                if ($row2['object_expire'] == 'true')
                {
                    if (strtotime($row2['object_expire_dt']) > 0)
                    {
                        $object_expires_on = $row2['object_expire_dt'];
                    }
                }
                
                if ($gsValues['SIM_NUMBER'] == 'false')
                {
                    $row2['sim_number'] = '';
                }
                
                $objects[] = array('name' => $row2['name'],
                                  'imei' => $row2['imei'],
                                  'active' => $row2['active'],
                                  'expires_on' => $object_expires_on,
                                  'model' => $row2['model'],
                                  'vin' => $row2['vin'],
                                  'plate_number' => $row2['plate_number'],
                                  'device' => $row2['device'],
                                  'sim_number' => $row2['sim_number']);
            }            
            
            $result[] = array('username' => $row['username'],
                              'email' => $row['email'],
                              'active' => $row['active'],
                              'expires_on' => $expires_on,
                              'privileges' => $privileges,
                              'api' => $row['api'],
                              'api_key' => $row['api_key'],
                              'dt_reg' => $row['dt_reg'],
                              'dt_login' => $row['dt_login'],
                              'ip' => $row['ip'],
                              'info' => $info,
                              'objects' => $objects);
        }
        
        header('Content-type: application/json');
        echo json_encode($result);
        die;
    }
        
    if ($command == 'ADD_USER')
    {
        loadLanguage('english');
        
        // command validation
        if (count($cmd) < 3)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        $send = strtolower($cmd[2]);
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        $privileges = array();
        $privileges['type'] = 'user';
        $privileges['map_osm'] = stringToBool($gsValues['USER_MAP_OSM']);
        $privileges['map_bing'] = stringToBool($gsValues['USER_MAP_BING']);
        $privileges['map_google'] = stringToBool($gsValues['USER_MAP_GOOGLE']);
        $privileges['map_google_street_view'] = stringToBool($gsValues['USER_MAP_GOOGLE_STREET_VIEW']);
        $privileges['map_google_traffic'] = stringToBool($gsValues['USER_MAP_GOOGLE_TRAFFIC']);
        $privileges['map_mapbox'] = stringToBool($gsValues['USER_MAP_MAPBOX']);
        $privileges['map_arcgis'] = stringToBool($gsValues['USER_MAP_ARCGIS']);
        $privileges['map_yandex'] = stringToBool($gsValues['USER_MAP_YANDEX']);
        $privileges['kml'] = stringToBool($gsValues['KML']);
        $privileges['dashboard'] = stringToBool($gsValues['DASHBOARD']);
        $privileges['history'] = stringToBool($gsValues['HISTORY']);
        $privileges['reports'] = stringToBool($gsValues['REPORTS']);
        $privileges['tachograph'] = stringToBool($gsValues['TACHOGRAPH']);
        $privileges['tasks'] = stringToBool($gsValues['TASKS']);
        $privileges['rilogbook'] = stringToBool($gsValues['RILOGBOOK']);
        $privileges['dtc'] = stringToBool($gsValues['DTC']);
        $privileges['maintenance'] = stringToBool($gsValues['MAINTENANCE']);
        $privileges['expenses'] = stringToBool($gsValues['EXPENSES']);
        $privileges['object_control'] = stringToBool($gsValues['OBJECT_CONTROL']);
        $privileges['image_gallery'] = stringToBool($gsValues['IMAGE_GALLERY']);
        $privileges['chat'] = stringToBool($gsValues['CHAT']);
        $privileges['subaccounts'] = stringToBool($gsValues['SUBACCOUNTS']);
        $privileges = json_encode($privileges);
        
        $result = addUser($send, 'true', 'false', '', $privileges, '', $email, $email, '', $gsValues['OBJ_ADD'], $gsValues['OBJ_LIMIT'], $gsValues['OBJ_LIMIT_NUM'], $gsValues['OBJ_DAYS'], $gsValues['OBJ_DAYS_NUM'], $gsValues['OBJ_EDIT'], $gsValues['OBJ_DELETE'], $gsValues['OBJ_HISTORY_CLEAR']);
    
        if ($result == 'OK')
        {
            echo 'success';
        }
        else if ($result == 'ERROR_NOT_SENT')
        {
            echo "ERROR: can't send e-mail";
        }
        else if ($result == 'ERROR_USERNAME_EXISTS')
        {
            echo 'ERROR: username exists';
        }
        else if ($result == 'ERROR_EMAIL_EXISTS')
        {
            echo 'ERROR: e-mail exists';
        }
        die;
    }
    
    if ($command == 'EDIT_USER')
    {
        loadLanguage('english');
        
        // command validation
        if (count($cmd) < 6)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
         // command parameters
        $email = strtolower($cmd[1]);
        $new_username = strtolower($cmd[2]);
        $new_email = strtolower($cmd[3]);
        $new_privileges = strtolower($cmd[4]);
        $new_manager_email = strtolower($cmd[5]);        
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        // get user id
        $user_id = '';
        $user_privileges = '';
        
        if ($email !== '')
        {
            $q = "SELECT * FROM `gs_users` WHERE `email`='".$email."'";
            $r = mysqli_query($ms, $q);
            
            if ($r)
            {
                $row = mysqli_fetch_array($r);
                $user_id = $row['id'];
                $user_privileges = array();
                $user_privileges = json_decode($row['privileges'], true);
                $user_privileges = checkUserPrivilegesArray($user_privileges);                
            }    
        }        
            
        if ($user_id == '')
        {
            echo 'ERROR: no user found with such e-mail';
            die;
        }
        
        // username
        if ($new_username !== 'false')
        {
            if (isEmailValid($new_username) || preg_match('/^[a-zA-Z0-9_]+$/', $new_username))
            {
                $q = "UPDATE `gs_users` SET `username`='".$new_username."' WHERE `id`='".$user_id."'";
                $r = mysqli_query($ms, $q);	 
            }
            else
            {
                echo 'ERROR: only numbers, letters and underscores are allowed in username';
                die;
            }
        }

        // email
        if ($new_email !== 'false')
        {
            if (isEmailValid($new_email))
            {
                $q = "SELECT * FROM `gs_users` WHERE `email`='".$new_email."' AND `id`<>'".$user_id."' LIMIT 1";
                $r = mysqli_query($ms, $q);
                $num = mysqli_num_rows($r);
                
                if ($num != 0)
                {
                    echo 'ERROR: e-mail exists';
                    die;
                }                
                
                $q = "UPDATE `gs_users` SET `email`='".$new_email."' WHERE `id`='".$user_id."'";
                $r = mysqli_query($ms, $q);	
            }
            else
            {
                echo 'ERROR: e-mail is not valid';
                die;
            }
        }
        
        // privileges
        if ($new_privileges !== 'false')
        {
            if ($new_privileges == 'manager')
            {
                $new_privileges_type = 'manager';
            }
            else  if ($new_privileges == 'user')
            {
                $new_privileges_type = 'user';
            }
            else  if ($new_privileges == 'viewer')
            {
                $new_privileges_type = 'viewer';
            }
            else
            {
                echo 'ERROR: wrong privileges';
                die;
            }
            
            $q = "SELECT * FROM `gs_users` WHERE `id`='".$user_id."'";
            $r = mysqli_query($ms, $q);
            if ($r)
            {
                $row = mysqli_fetch_array($r);
                
                $privileges = array();
                $privileges = json_decode($row['privileges'], true);
                $privileges = checkUserPrivilegesArray($privileges);
                
                $privileges['type'] = $new_privileges_type;
                
                $privileges = json_encode($privileges);
                
                if ($new_privileges_type == 'manager')
                {
                    $manager_id = $user_id;
                    $q = "UPDATE `gs_users` SET `privileges`='".$privileges."', `manager_id`='".$manager_id."', `manager_billing`='false' WHERE `id`='".$user_id."'";
                    $r = mysqli_query($ms, $q);
                }
                else
                {                   
                    $q = "UPDATE `gs_users` SET `privileges`='".$privileges."', `manager_id`='0', `manager_billing`='false' WHERE `id`='".$user_id."'";
                    $r = mysqli_query($ms, $q);
                }                
            }   
        }
        
        // manager
        if (($new_privileges !== 'manager') && ($new_manager_email !== 'false'))
        {
            if ($new_manager_email == '0')
            {
                if (($user_privileges['type'] == 'user') || ($user_privileges['type'] == 'viewer'))
                {
                    $q = "UPDATE `gs_users` SET `manager_id`='0' WHERE `id`='".$user_id."'";
                    $r = mysqli_query($ms, $q);       
                }                
            }
            else
            {
                $q = "SELECT * FROM `gs_users` WHERE `email`='".$new_manager_email."'";
                $r = mysqli_query($ms, $q);

                if ($r)
                {
                    $row = mysqli_fetch_array($r);
                    
                    $privileges = array();
                    $privileges = json_decode($row['privileges'], true);
                    $privileges = checkUserPrivilegesArray($privileges);
                    
                    if ($privileges['type'] == 'manager')
                    {
                        $manager_id = $row['id'];
                        
                        $q = "UPDATE `gs_users` SET `manager_id`='".$manager_id."' WHERE `id`='".$user_id."'";
                        $r = mysqli_query($ms, $q);    
                    }
                    else
                    {
                        echo 'ERROR: no manager found with such e-mail';
                        die;
                    }
                }
                else
                {
                    echo 'ERROR: no manager found with such e-mail';
                    die;
                }
            }
        }
        
        echo 'success';
        die;
    }
        
    if ($command == 'DEL_USER')
    {
        // command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        // get user id from email
        $user_id = getUserIdFromEmail($email);
        
        if (!$user_id)
        {
            echo 'ERROR: no user found with such e-mail';
            die;  
        }
        
        // delete user
        delUser($user_id);
        
        echo 'success';
        die;
    }
    
    if ($command == 'USER_SET_ACTIVITY')
    {
        // command validation
        if (count($cmd) < 5)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        $active = strtolower($cmd[2]);
        $account_expire = $cmd[3];
        $account_expire_dt = $cmd[4];
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }

        // get user id from email
        $user_id = getUserIdFromEmail($email);
        
        if (!$user_id)
        {
            echo 'ERROR: no user found with such e-mail';
            die;  
        }
        
        if ($account_expire == '')
        {
            echo "ERROR: expire can't be empty";
            die;
        }
        
        if (($account_expire == 'true') && ($account_expire_dt == ''))
        {
            echo "ERROR: expire date can't be empty";
            die;
        }
        
        // command exec               
        if ($active == 'true')
        {                        
            $q = "UPDATE `gs_users` SET `active`='true', `account_expire`='".$account_expire."', `account_expire_dt`='".$account_expire_dt."' WHERE `id`='".$user_id."'";
        }
        else if ($active == 'false')
        {
            $q = "UPDATE `gs_users` SET `active`='false', `account_expire`='".$account_expire."', `account_expire_dt`='".$account_expire_dt."' WHERE `id`='".$user_id."'";
        }
        $r = mysqli_query($ms, $q);
        
        echo 'success';
        die;
    }
    
    if ($command == 'GET_OBJECTS')
    {
        // command validation
        if (count($cmd) < 1)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        $result = array();
        
        $q = "SELECT * FROM `gs_objects` ORDER BY imei ASC";
        $r = mysqli_query($ms, $q);
        
        while($row = mysqli_fetch_array($r))
        {
            $expires_on = '';
            
            if ($row['object_expire'] == 'true')
            {
                if (strtotime($row['object_expire_dt']) > 0)
                {
                    $expires_on = $row['object_expire_dt'];
                }
            }
            
            if ($gsValues['SIM_NUMBER'] == 'false')
            {
                $row['sim_number'] = '';
            }
            
            $result[] = array('name' => $row['name'],
                              'imei' => $row['imei'],
                              'active' => $row['active'],
                              'expires_on' => $expires_on,
                              'model' => $row['model'],
                              'vin' => $row['vin'],
                              'plate_number' => $row['plate_number'],
                              'device' => $row['device'],
                              'sim_number' => $row['sim_number']);
        }
        
        header('Content-type: application/json');
        echo json_encode($result);
        die;
    }
    
    if ($command == 'ADD_OBJECT')
    {
        // command validation
        if (count($cmd) < 5)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $imei = strtoupper($cmd[1]);
        $name = $cmd[2];
        $object_expire = $cmd[3];
        $object_expire_dt = $cmd[4];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($name == '')
        {
            echo "ERROR: name can't be empty";
            die;
        }
        
        if ($object_expire == '')
        {
            echo "ERROR: expire can't be empty";
            die;
        }
        
        if (($object_expire == 'true') && ($object_expire_dt == ''))
        {
            echo "ERROR: expire date can't be empty";
            die;
        }
        
        if(checkObjectLimitSystem())
        {
            echo "ERROR: system object limit is reached";
            die;
        }
        
        // add object
        addObjectSystem($name, $imei, 'true', $object_expire, $object_expire_dt, '0');
        createObjectDataTable($imei);
        
        echo 'success';
        die;
    }
        
    if ($command == 'DEL_OBJECT')
    {
        // command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $imei = strtoupper($cmd[1]);
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        // delete object
        delObjectSystem($imei);
        
        echo 'success';
        die;
    }
    
    if ($command == 'ADD_USER_OBJECT')
    {
        // command validation
        if (count($cmd) < 3)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        $imei = strtoupper($cmd[2]);
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        // get user id from email
        $user_id = getUserIdFromEmail($email);
        
        if (!$user_id)
        {
            echo 'ERROR: no user found with such e-mail';
            die;  
        }
        
        // add object to user
        addObjectUser($user_id, $imei, 0, 0, 0);
        
        echo 'success';
        die;
    }
    
    if ($command == 'DEL_USER_OBJECT')
    {
        // command validation
        if (count($cmd) < 3)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        $imei = strtoupper($cmd[2]);
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        // get user id from email
        $user_id = getUserIdFromEmail($email);
        
        if (!$user_id)
        {
            echo 'ERROR: no user found with such e-mail';
            die;  
        }
        
        // delete object from user
        delObjectUser($user_id, $imei);
        
        echo 'success';
        die;
    }
        
    if ($command == 'OBJECT_SET_ACTIVITY')
    {
        // command validation
        if (count($cmd) < 5)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $imei = strtoupper($cmd[1]);
        $active = strtolower($cmd[2]);
        $object_expire = $cmd[3];
        $object_expire_dt = $cmd[4];
        
        if (!ctype_alnum($imei))
        {
            echo 'ERROR: IMEI is not valid';
            die;
        }
        
        if ($active == '')
        {
            echo "ERROR: active can't be empty";
            die;
        }
        
        if ($object_expire == '')
        {
            echo "ERROR: expire can't be empty";
            die;
        }
        
         if (($object_expire == 'true') && ($object_expire_dt == ''))
        {
            echo "ERROR: expire date can't be empty";
            die;
        }
        
        // command exec               
        if ($active == 'true')
        {                        
            $q = "UPDATE `gs_objects` SET `active`='true', `object_expire`='".$object_expire."', `object_expire_dt`='".$object_expire_dt."' WHERE `imei`='".$imei."'";
        }
        else if ($active == 'false')
        {
            $q = "UPDATE `gs_objects` SET `active`='false', `object_expire`='".$object_expire."', `object_expire_dt`='".$object_expire_dt."' WHERE `imei`='".$imei."'";
        }
        $r = mysqli_query($ms, $q);
        
        echo 'success';
        die;
    }
    
    if ($command == 'ADD_USER_BILLING_PLAN')
    {
        // command validation
        if (count($cmd) < 3)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        $plan_id = $cmd[2];
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        if ($plan_id == '')
        {
            echo "ERROR: plan ID can't be empty";
            die;
        }
        
        // command exec
        $user_id = getUserIdFromEmail($email);
        
        if (!$user_id)
        {
            echo 'ERROR: no user found with such e-mail';
            die;  
        }
            
        $dt_purchase = gmdate("Y-m-d H:i:s");
            
        $q = "SELECT * FROM `gs_billing_plans` WHERE `plan_id`='".$plan_id."'";
        $r = mysqli_query($ms, $q);
            
        if (!$r)
        {
            echo 'ERROR: no plan found with such plan ID';
            die;  
        }
            
        $row = mysqli_fetch_array($r);
            
        $name = $row['name'];
        $active = $row['active'];
        $objects = $row['objects'];
        $period = $row['period'];
        $period_type = $row['period_type'];
        $price = $row['price'];
        
        if ($active == 'true')
        {
            $q = "INSERT INTO `gs_user_billing_plans` (`user_id`,
                                                        `dt_purchase`,
                                                        `name`,
                                                        `objects`,
                                                        `period`,
                                                        `period_type`,
                                                        `price`
                                                        ) VALUES (
                                                        '".$user_id."',
                                                        '".$dt_purchase."',
                                                        '".$name."',
                                                        '".$objects."',
                                                        '".$period."',
                                                        '".$period_type."',
                                                        '".$price."')";
            $r = mysqli_query($ms, $q);
            
            echo 'ERROR: plan is not active';
            die;
        }
        
        echo 'success';
        die;
    }
    
    if ($command == 'GET_USER_API_KEY')
    {
        // command validation
        if (count($cmd) < 2)
        {
            echo 'ERROR: missing command parameters';
            die;
        }
        
        // command parameters
        $email = strtolower($cmd[1]);
        
        if (!isEmailValid($email))
        {
            echo 'ERROR: e-mail is not valid';
            die;
        }
        
        // get user api key from email
        $api_key = getUserAPIKeyFromEmail($email);
        
        if (!$api_key)
        {
            echo 'ERROR: no user found with such e-mail';
            die;  
        }
        
        echo $api_key;
        die;
    }
    
    echo 'ERROR: unknown command';
    die;
?>