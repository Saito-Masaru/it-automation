<?php
//   Copyright 2019 NEC Corporation
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//
/**
 * 【処理内容】
 *    メニュー作成機能の独自バリデータ
 *
 */


/**
* メニュー名専用のバリデータクラス
*/

class MenuNameValidator extends SingleTextValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                if(rtrim($arrayRegData['MENU_NAME']) === rtrim($g['objMTS']->getSomeMessage("ITAWDCH-MNU-1100001"))){
                    // メニュー名が「メインメニュー」
                    $retBool = false;
                    $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1001");
                }
            }

            if($boolCheckContinue===true){

                // 禁止文字チェック
                mb_regex_encoding("UTF-8");
                if( preg_match('/[\\\\\/\:\*?"<>|\[\]：￥／＊［］]+/u', $value) === 1){
                    $retBool = false;
                    $boolCheckContinue = false;
                    $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1020");
                }
            }

            if( $retBool === false ){
                $this->setValidRule($strErrAddMsg);
            }
        }
        return $retBool;
    }
}

/**
* 作成対象専用のバリデータクラス
*/

class SubstitutionValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;
        
        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }
        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }
        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){
                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                // 用途を取得
                $purpose = array_key_exists('PURPOSE',$arrayRegData)?$arrayRegData['PURPOSE']:null;
                // データシート用メニューグループを取得
                $menugroupForData = array_key_exists('MENUGROUP_FOR_CMDB',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_CMDB']:null;
                // ホストグループ用メニューグループを取得
                $menugroupForHG = array_key_exists('MENUGROUP_FOR_HG',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_HG']:null;
                // ホスト用メニューグループを取得
                $menugroupForH = array_key_exists('MENUGROUP_FOR_H',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_H']:null;
                // 参照用メニューグループを取得
                $menugroupForView= array_key_exists('MENUGROUP_FOR_VIEW',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_VIEW']:null;
                // 縦管理メニューグループを取得
                $menugroupForConv = array_key_exists('MENUGROUP_FOR_CONV',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_CONV']:null;

                // 作成対象で「データシート」を選択
                if("2" == $value){  
                    // 用途が選択されている場合、エラー
                    if($purpose){  
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1026");
                    }
                    // データシート用メニューグループが未選択の場合、エラー
                    else if(!$menugroupForData){  
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1025");
                    }
                    // ホストグループ用メニューグループ、ホスト用メニューグループ、
                    // 参照用メニューグループ、縦管理メニューグループが選択されている場合、エラー
                    else if($menugroupForHG || $menugroupForH || $menugroupForView || $menugroupForConv){  
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1028");
                    }
                }
                // 作成対象で「パラメータシート」を選択
                else if("1" == $value){ 
                    //用途が未選択の場合、エラー
                    if(!$purpose){ 
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1022");
                    } 
                    // データシート用メニューグループが選択されている場合、エラー
                    else if($menugroupForData){ 
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1027");
                    } 
                } 

                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* 用途専用のバリデータクラス
*/

class PurposeValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                  // 作成対象を取得
                $target = array_key_exists('TARGET',$arrayRegData)?$arrayRegData['TARGET']:null;
                // ホストグループ用メニューグループを取得
                $menugroupForHG = array_key_exists('MENUGROUP_FOR_HG',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_HG']:null;
                // ホスト用メニューグループを取得
                $menugroupForHost = array_key_exists('MENUGROUP_FOR_H',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_H']:null;
                // 参照用メニューグループを取得
                $menugroupForView = array_key_exists('MENUGROUP_FOR_VIEW',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_VIEW']:null;

                // 作成対象が「パラメータシート」選択時のみ
                if("1" == $target){
                    // 用途がホストグループ用の場合
                    if("2" == $value){

                        // ホストグループ機能がインストールされていない場合、エラー
                        if(!file_exists("{$g['root_dir_path']}/libs/release/ita_hostgroup")){
                            $retBool = false;
                            $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1003");
                        }
                        // ホストグループ用メニューグループ、ホスト用メニューグループ、参照用メニューグループが設定されていない場合、エラー
                        else if(!$menugroupForHG || !$menugroupForHost || !$menugroupForView){
                            $retBool = false;
                            $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1023");
                        }
                    }
                    // 用途がホストの場合
                    else if("1" == $value){

                        // ホストグループ用メニューグループが設定されている場合、エラー
                        if($menugroupForHG){
                            $retBool = false;
                            $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1002");
                        }
                        // ホスト用メニューグループ、または参照用メニューグループが設定されていない場合、エラー
                        else if(!$menugroupForHost || !$menugroupForView){
                            $retBool = false;
                            $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1024");
                        }

                    }
                }
                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* ホストグループ用メニューグループ専用のバリデータクラス
*/

class MgForHgValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                // 用途がホストグループ用の場合
                if(array_key_exists('PURPOSE',$arrayRegData) && "2" == $arrayRegData['PURPOSE']){

                    // ホスト用メニューグループを取得
                    $menugroupForH = array_key_exists('MENUGROUP_FOR_H',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_H']:null;
                    // 最新値参照用メニューグループを取得
                    $menugroupForView = array_key_exists('MENUGROUP_FOR_VIEW',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_VIEW']:null;
                    // 縦管理メニュー用メニューグループを取得
                    $menugroupForConv = array_key_exists('MENUGROUP_FOR_CONV',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_CONV']:null;

                    // 他のメニューグループと同じ場合、エラー
                    if($value && ($value == $menugroupForH || $value == $menugroupForView || $value == $menugroupForConv)){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1005");
                    }
                }
                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* ホスト用メニューグループ専用のバリデータクラス
*/

class MgForHostValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                // 値が設定されている場合
                if("" != $value){
                    // ホストグループ用メニューグループを取得
                    $menugroupForHg = array_key_exists('MENUGROUP_FOR_HG',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_HG']:null;
                    // 最新値参照用メニューグループを取得
                    $menugroupForView  = array_key_exists('MENUGROUP_FOR_VIEW',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_VIEW']:null;
                    // 縦管理メニュー用メニューグループを取得
                    $menugroupForConv = array_key_exists('MENUGROUP_FOR_CONV',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_CONV']:null;

                    // 他のメニューグループと同じ場合、エラー
                    if($value == $menugroupForHg || $value == $menugroupForView || $value == $menugroupForConv){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1005");
                    }
                }
                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* 最新値参照用メニューグループ専用のバリデータクラス
*/

class MgForViewValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                // 値が設定されている場合
                if("" != $value){

                    // ホストグループ用メニューグループを取得
                    $menugroupForHg = array_key_exists('MENUGROUP_FOR_HG',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_HG']:null;
                    // ホスト用メニューグループを取得
                    $menugroupForH  = array_key_exists('MENUGROUP_FOR_H',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_H']:null;
                    // 縦管理メニュー用メニューグループを取得
                    $menugroupForConv = array_key_exists('MENUGROUP_FOR_CONV',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_CONV']:null;

                    // 他のメニューグループと同じ場合、エラー
                    if($value == $menugroupForHg || $value == $menugroupForH || $value == $menugroupForConv){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1005");
                    }
                }
                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* 縦管理メニュー用メニューグループ専用のバリデータクラス
*/

class MgForConvValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                // 値が設定されている場合
                if("" != $value){

                    // ホストグループ用メニューグループを取得
                    $menugroupForHg = array_key_exists('MENUGROUP_FOR_HG',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_HG']:null;
                    // ホスト用メニューグループを取得
                    $menugroupForH  = array_key_exists('MENUGROUP_FOR_H',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_H']:null;
                    // 最新値参照用メニューグループを取得
                    $menugroupForView = array_key_exists('MENUGROUP_FOR_VIEW',$arrayRegData)?$arrayRegData['MENUGROUP_FOR_VIEW']:null;

                    // 他のメニューグループと同じ場合、エラー
                    if($value == $menugroupForHg || $value == $menugroupForH || $value == $menugroupForView){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1005");
                    }
                }
                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* 入力方式専用のバリデータクラス
*/

class InputMethodValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $strErrAddMsg = "";

                // 入力方式が文字列(単一行)の場合
                if("1" == $value){
                    // 文字列(単一行)最大バイト数が設定されていない場合、エラー
                    if($retBool == true && (!array_key_exists('MAX_LENGTH',$arrayRegData) || "" == $arrayRegData['MAX_LENGTH'])){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1009");
                    }
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1034");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1035");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1037");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1036");
                    } 
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1039");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1038");
                    } 
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1040");
                    }
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1010");
                    }
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1094");
                    }
                }
                // 入力方式が文字列(複数行)の場合
                else if("2" == $value){
                    // 文字列(複数行)最大バイト数が設定されていない場合、エラー
                    if($retBool == true && (!array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) || "" == $arrayRegData['MULTI_MAX_LENGTH'])){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1065");
                    }
                    // 文字列(単一行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1066");
                    }
                    // 文字列(単一行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1067");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1070");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1069");
                    } 
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1072");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1071");
                    } 
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1073");
                    }
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1068");
                    }
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1095");
                    }
                }
                // 入力方式が整数の場合
                else if("3" == $value){
                    // 文字列(単一行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1048");
                    }
                    // 文字列(単一行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1049");
                    } 
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1050");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1051");
                    }
                    // 整数最小値が設定されていない場合、一時最小値にする
                    if($retBool == true && (!array_key_exists('INT_MIN',$arrayRegData) || "" == $arrayRegData['INT_MIN'])){
                        $arrayRegData['INT_MIN'] = "-2147483648";
                    }
                    // 整数最大値が設定されていない場合、一時最大値にする(最大値>最小値チェックの時エラー出ないため)
                    if($retBool == true && (!array_key_exists('INT_MAX',$arrayRegData) || "" == $arrayRegData['INT_MAX'])){
                        $arrayRegData['INT_MAX'] = "2147483648";
                    }
                     // 最大値<最小値になっている場合、エラー
                    if($retBool == true && $arrayRegData['INT_MIN'] > $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1052",[$arrayRegData['INT_MIN'],$arrayRegData['INT_MAX']]);
                    }
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1053");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1054");
                    } 
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1055");
                    }
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1056");
                    }
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1096");
                    }
                }
                // 入力方式が小数の場合
                else if("4" == $value){
                    // 文字列(単一行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1057");
                    }
                    // 文字列(単一行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1058");
                    } 
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1059");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1060");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1061");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1062");
                    } 
                    // 小数最小値が設定されていない場合、一時最小値にする
                    if($retBool == true && (!array_key_exists('FLOAT_MIN',$arrayRegData) || "" == $arrayRegData['FLOAT_MIN'])){
                        $arrayRegData['FLOAT_MIN'] = "-99999999999999";
                    }
                    // 小数最大値が設定されていない場合、一時最大値にする
                    if($retBool == true && (!array_key_exists('FLOAT_MAX',$arrayRegData) || "" == $arrayRegData['FLOAT_MAX'])){
                        $arrayRegData['FLOAT_MAX'] = "99999999999999";
                    }
                    // 最大値<最小値になっている場合、エラー
                    if($retBool == true && $arrayRegData['FLOAT_MIN'] > $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1063",[$arrayRegData['FLOAT_MIN'],$arrayRegData['FLOAT_MAX']]);
                    } 
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1064");
                    }
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1097");
                    }
                }
                // 入力方式が日時の場合
                else if("5" == $value){
                    // 文字列(単一行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1074");
                    }
                    // 文字列(単一行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1075");
                    }
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1076");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1077");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1078");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1079");
                    } 
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1080");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1081");
                    }
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1082");
                    } 
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1083");
                    }
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1098");
                    }
                }
                // 入力方式が日付の場合
                else if("6" == $value){
                    // 文字列(単一行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1084");
                    }
                    // 文字列(単一行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1085");
                    }
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1086");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1087");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1088");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1089");
                    } 
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1090");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1091");
                    } 
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1092");
                    } 
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1093");
                    } 
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1099");
                    }
                }
                // 入力方式がプルダウンの場合
                else if("7" == $value){
                    // 最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1011");
                    }
                    // 正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1012");
                    }
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1041");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1042");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1044");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1043");
                    } 
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1046");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1045");
                    } 
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1047");
                    }
                    // メニューグループ：メニュー：項目が設定されていない場合、エラー
                    if($retBool == true && (!array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) || "" == $arrayRegData['OTHER_MENU_LINK_ID'])){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1013");
                    }
                    // 文字列(PW)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PW_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['PW_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1100");
                    }
                }
                // 入力方式が文字列(PW)の場合
                else if("8" == $value){
                    // 文字列(単一行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1102");
                    }
                    // 文字列(単一行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('PREG_MATCH',$arrayRegData) && "" != $arrayRegData['PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1103");
                    }
                    // 文字列(複数行)最大バイト数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_MAX_LENGTH',$arrayRegData) && "" != $arrayRegData['MULTI_MAX_LENGTH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1104");
                    }
                    // 文字列(複数行)正規表現が設定されている場合、エラー
                    if($retBool == true && array_key_exists('MULTI_PREG_MATCH',$arrayRegData) && "" != $arrayRegData['MULTI_PREG_MATCH']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1105");
                    }
                    // 整数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MIN',$arrayRegData) && "" != $arrayRegData['INT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1106");
                    } 
                    // 整数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('INT_MAX',$arrayRegData) && "" != $arrayRegData['INT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1107");
                    } 
                    // 小数最小値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MIN',$arrayRegData) && "" != $arrayRegData['FLOAT_MIN']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1108");
                    } 
                    // 小数最大値が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_MAX',$arrayRegData) && "" != $arrayRegData['FLOAT_MAX']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1109");
                    } 
                    // 小数桁数が設定されている場合、エラー
                    if($retBool == true && array_key_exists('FLOAT_DIGIT',$arrayRegData) && "" != $arrayRegData['FLOAT_DIGIT']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1110");
                    }
                    // メニューグループ：メニュー：項目が設定されている場合、エラー
                    if($retBool == true && array_key_exists('OTHER_MENU_LINK_ID',$arrayRegData) && "" != $arrayRegData['OTHER_MENU_LINK_ID']){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1111");
                    }
                    // 文字列(PW)最大バイト数が設定されていない場合、エラー
                    if($retBool == true && (!array_key_exists('PW_MAX_LENGTH',$arrayRegData) || "" == $arrayRegData['PW_MAX_LENGTH'])){
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1101");
                    }
                }
                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* 対象メニュー名:開始項目名専用のバリデータクラス
*/

class StartCreateItemValidator extends IDValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";
        $modeValue_sub = "";
        $boolCheckContinue = true;

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                $this->strModeIdOfLastErr = $strModeId;
                $this->strErrAddMsg = "";

                $query01 = "SELECT CONVERT_PARAM_ID "
                            ." FROM F_CONVERT_PARAM_INFO "
                            ." WHERE CREATE_ITEM_ID IN "
                            ." (SELECT CREATE_ITEM_ID FROM G_CREATE_ITEM_INFO WHERE DISUSE_FLAG = '0' AND "
                            ."                                                      CREATE_MENU_ID = "
                            ."                                                      (SELECT CREATE_MENU_ID FROM G_CREATE_ITEM_INFO "
                            ."                                                       WHERE DISUSE_FLAG = '0' AND CREATE_ITEM_ID=:CREATE_ITEM_ID)) "
                            ." AND DISUSE_FLAG = '0' "
                            ." AND CONVERT_PARAM_ID <> :CONVERT_PARAM_ID ";

                if(array_key_exists('CONVERT_PARAM_ID',$arrayVariant['edit_target_row'])){
                    $aryForBind01['CONVERT_PARAM_ID'] = $arrayVariant['edit_target_row']['CONVERT_PARAM_ID'];
                }
                else{
                    $aryForBind01['CONVERT_PARAM_ID'] = 0;
                }
                $aryForBind01['CREATE_ITEM_ID'] = $arrayRegData['CREATE_ITEM_ID'];

                // SQL発行
                $retArray01 = singleSQLExecuteAgent($query01, $aryForBind01, "");

                if( $retArray01[0] === true ){
                    $objQuery01 =& $retArray01[1];
                    $intCount01 = 0;
                    $aryDiscover01 = array();
                    while($row01 = $objQuery01->resultFetch()){
                        $intCount01 += 1;
                        $aryDiscover01[] = $row01;
                    }
                    unset($objQuery01);

                    if($intCount01 > 0){
                        // 最大バイト数の上限オーバー
                        $retBool = false;
                        $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1019", $aryDiscover01[0]['CONVERT_PARAM_ID']);
                    }
                }
                else{
                    // DBエラー
                    $retBool = false;
                    $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1007", $retArray01[2]);
                }

                if( $retBool === false ){
                    $this->setValidRule($strErrAddMsg);
                }
            }
        }
        return $retBool;
    }
}

/**
* 正規表現専用のバリデータクラス
*/

class PregMatchValidator extends SingleTextValidator {

    protected $eventMasterName;

    function isValid($value, $strNumberForRI=null, $arrayRegData=null, &$arrayVariant=array()){

        global $g;
        $retBool = true;
        $strModeId = "";

        if( parent::isValid($value, $strNumberForRI, $arrayRegData, $arrayVariant) != true ) {
            return false;
        }

        if(array_key_exists("TCA_PRESERVED", $arrayVariant)){
            if(array_key_exists("TCA_ACTION", $arrayVariant["TCA_PRESERVED"])){
                $aryTcaAction = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"];
                $strModeId = $aryTcaAction["ACTION_MODE"];
            }
        }

        if( $strModeId != "" ){
            $boolCheckContinue = false;
            if($strModeId == "DTUP_singleRecRegister" ){
                //----各種登録時
                $boolCheckContinue = true;
                //各種登録時----
            }else if($strModeId == "DTUP_singleRecUpdate"){
                //----各種更新時
                $boolCheckContinue = true;
                //各種更新時----
            }else if($strModeId == "DTUP_singleRecDelete"){
                $modeValue_sub = $arrayVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_SUB_MODE"];//['mode_sub'];
                if( $modeValue_sub=="on" ){
                    //処理をしない
                }else if( $modeValue_sub=="off" ){
                    //復活時
                    $boolCheckContinue = true;
                }
            }else{
                //処理をしない
            }

            if($boolCheckContinue===true){

                // 正規表現が不正な文法
                if("" != $value && false === @preg_match($value, "")){
                    $retBool = false;
                    $strErrAddMsg = $g['objMTS']->getSomeMessage("ITACREPAR-ERR-1021");
                }
            }

            if( $retBool === false ){
                $this->setValidRule($strErrAddMsg);
            }
        }
        return $retBool;
    }
}
?>
