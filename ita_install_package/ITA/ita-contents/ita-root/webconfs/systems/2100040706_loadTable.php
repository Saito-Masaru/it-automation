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
//////////////////////////////////////////////////////////////////////
//
//  【処理概要】
//    ・Ansible 共通 グローバル変数管理
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940000");
/*
Ansible 共通 グローバル変数管理
*/
    $tmpAry = array(
        'TT_SYS_01_JNL_SEQ_ID'=>'JOURNAL_SEQ_NO',
        'TT_SYS_02_JNL_TIME_ID'=>'JOURNAL_REG_DATETIME',
        'TT_SYS_03_JNL_CLASS_ID'=>'JOURNAL_ACTION_CLASS',
        'TT_SYS_04_NOTE_ID'=>'NOTE',
        'TT_SYS_04_DISUSE_FLAG_ID'=>'DISUSE_FLAG',
        'TT_SYS_05_LUP_TIME_ID'=>'LAST_UPDATE_TIMESTAMP',
        'TT_SYS_06_LUP_USER_ID'=>'LAST_UPDATE_USER',
        'TT_SYS_NDB_ROW_EDIT_BY_FILE_ID'=>'ROW_EDIT_BY_FILE',
        'TT_SYS_NDB_UPDATE_ID'=>'WEB_BUTTON_UPDATE',
        'TT_SYS_NDB_LUP_TIME_ID'=>'UPD_UPDATE_TIMESTAMP'
    );

    $table = new TableControlAgent('B_ANS_GLOBAL_VARS_MASTER','GBL_VARS_NAME_ID', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940001"), 'B_ANS_GLOBAL_VARS_MASTER_JNL', $tmpAry);
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['GBL_VARS_NAME_ID']->setSequenceID('B_ANS_GLOBAL_VARS_MASTER_RIC');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('B_ANS_GLOBAL_VARS_MASTER_JSQ');
    unset($tmpAryColumn);

    // QMファイル名プレフィックス
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940002"));
    // エクセルのシート名
    $table->getFormatter('excel')->setGeneValue('sheetNameForEditByFile',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940003"));

    $table->setAccessAuth(true);    // データごとのRBAC設定


    $objVldt = new TextValidator(1, 128, false, '/^GBL_[_a-zA-Z0-9]+$/', $g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940011"));
    $objVldt->setRegexp("/^[^\r\n]*$/s","DTiS_filterDefault");
    $c = new TextColumn('VARS_NAME',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940010"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940011"));//エクセル・ヘッダでの説明
    $c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $c->setUnique(true);
    $table->addColumn($c);

    $objVldt = new MultiTextValidator(1,8192,false);
    $c = new MultiTextColumn('VARS_ENTRY',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940020"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940021"));//エクセル・ヘッダ>での説明
    $c->setValidator($objVldt);
    $c->setRequired(true);//登録/更新時には、入力必須
    $table->addColumn($c);

    $objVldt = new SingleTextValidator(0,256,false);
    $c = new TextColumn('VARS_DESCRIPTION',$g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940030"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAANSIBLEH-MNU-1940031"));//エクセル・ヘッダでの説明
    $c->setValidator($objVldt);
    $table->addColumn($c);

    $table->fixColumn();

    // 登録/更新/廃止/復活があった場合、データベースを更新した事をマークする。
    $tmpObjFunction = function($objColumn, $strEventKey, &$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
        $boolRet = true;
        $intErrorType = null;
        $aryErrMsgBody = array();
        $strErrMsg = "";
        $strErrorBuf = "";
        $strFxName = "";

        $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
        if( $modeValue=="DTUP_singleRecRegister" || $modeValue=="DTUP_singleRecUpdate" || $modeValue=="DTUP_singleRecDelete" ){

            $strQuery = "UPDATE A_PROC_LOADED_LIST "
                       ."SET LOADED_FLG='0' ,LAST_UPDATE_TIMESTAMP = NOW(6) "
                       ."WHERE ROW_ID IN (2100020001,2100020003,2100020005) ";
            $aryForBind = array();
            $aryRetBody = singleSQLExecuteAgent($strQuery, $aryForBind, $strFxName);

            if( $aryRetBody[0] !== true ){
                $boolRet = false;
                $strErrMsg = $aryRetBody[2];
                $intErrorType = 500;
            }
        }
        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
        return $retArray;
    };
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['GBL_VARS_NAME_ID']->setFunctionForEvent('beforeTableIUDAction',$tmpObjFunction);

    $table->setGeneObject('webSetting', $arrayWebSetting);

    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
