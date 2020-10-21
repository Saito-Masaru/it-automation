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
//  【処理概要】
//    ・本体tableのPKをtext(varchar等)にする
//    ・journal tableを使わないがcolumn class自体はdummyで存在させる(journalTableへの更新系でのSQLはskipする)
//    ・start transaction直後のsequenceへのselect .. for updateをskipする
//////////////////////////////////////////////////////////////////////

class RowIdentifyTextColumn extends TextColumn {
    protected $uniqueColumns;
    protected $strSequenceId;

    //----ここから継承メソッドの上書き処理

    function __construct($strColId, $strColLabel, $strSequenceId=null, $uniqueColumns=array()) {
        global $g;
        
        parent::__construct($strColId, $strColLabel, $strSequenceId);
        $this->strSequenceId = $strSequenceId;
        $this->setHiddenMainTableColumn(true);
        $this->setHeader(true);
        //$this->setSearchType("like");

        $outputType = new OutputType(new ReqTabHFmt(), new TextTabBFmt());
        $this->setOutputType("update_table", $outputType);
        //自動入力
        $outputType = new OutputType(new ReqTabHFmt(), new StaticTextTabBFmt($g['objMTS']->getSomeMessage("ITAWDCH-STD-11401")));
        $this->setOutputType("register_table", $outputType);

        //----このインスタンスに紐づくOutputTypeインスタンスにアクセスする
        $this->getOutputType("delete_table")->init($this, "delete_table");
        $this->getOutputType("filter_table")->init($this, "filter_table");
        $this->getOutputType("print_table")->init($this, "print_table");
        //$this->getOutputType("print_journal_table")->init($this, "print_journal_table");
        //このインスタンスに紐づくOutputTypeインスタンスにアクセスする----

        $this->setDescription($g['objMTS']->getSomeMessage("ITAWDCH-STD-11402"));

        //$this->setJournalSearchFilter(true);

        $this->setValidator(new SingleTextValidator(0,256));
    }

    //----AddColumnイベント系
    function initTable($objTable, $colNo=null) {
        parent::initTable($objTable, $colNo);
    }
    //AddColumnイベント系----

    //----TableIUDイベント系
    public function beforeIUDValidateCheck(&$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()) {
        $boolRet = false;
        $intErrorType = null;
        $aryErrMsgBody = array();
        $strErrMsg = "";
        $strErrorBuf = "";

        $modeValue = $aryVariant["TCA_PRESERVED"]["TCA_ACTION"]["ACTION_MODE"];
        if( $modeValue=="DTUP_singleRecRegister" ){
            //----親クラス[AutoNumColumn]の同名関数を呼んで、その後作業
            $retArray = parent::beforeIUDValidateCheck($exeQueryData, $reqOrgData, $aryVariant);
            //親クラス[AutoNumColumn]の同名関数を呼んで、その後作業----
        }else if( $modeValue=="DTUP_singleRecUpdate" ){
            //----更新の場合
            $boolRet = true;
            $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
            //更新の場合----
        }else if( $modeValue=="DTUP_singleRecDelete" ){
            //----廃止の場合
            $boolRet = true;
            $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
            //廃止の場合----
        }
        return $retArray;
    }
    //TableIUDイベント系----

    //ここまで継承メソッドの上書き処理----

    //----ここから新規メソッドの定義宣言処理
    public function setSequenceID($strSequenceId){
        $this->strSequenceId = $strSequenceId;
    }
    function getSequenceID() {
        return $this->strSequenceId;
    }
    //ここまで新規メソッドの定義宣言処理----
}

class JournalSeqNoColumnDummy extends TextColumn {
    //通常時は表示しない

    protected $strSequenceId;

    //----ここから継承メソッドの上書き処理

    function __construct($strColId="JOURNAL_SEQ_NO", $strColExplain="", $strSequenceId=null){
        if( $strColExplain == "" ){
            $strColExplain = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11301");
        }
        parent::__construct($strColId, $strColExplain);
        $this->setNum(true);
        $this->setSubtotalFlag(false);
        $this->setHeader(true);
        $this->setDBColumn(false);
        $this->getOutputType("print_journal_table")->setVisible(true);
        $this->getOutputType("update_table")->setVisible(false);
        $this->getOutputType("register_table")->setVisible(false);
        $this->getOutputType("filter_table")->setVisible(false);
        $this->getOutputType("print_table")->setVisible(false);
        $this->getOutputType("delete_table")->setVisible(false);
        $this->getOutputType("excel")->setVisible(false);
        $this->getOutputType("csv")->setVisible(false);
        $this->getOutputType("json")->setVisible(false);

        $this->setSequenceID($strSequenceId);
        //$this->setNumberSepaMarkShow(false);
    }

    //----FixColumnイベント系
    function afterFixColumn(){
        if($this->getSequenceID() === null){
            $arrayColumn = $this->objTable->getColumns();
            $objRIColumnID = $arrayColumn[$this->objTable->getRowIdentifyColumnID()];
            $strSeqId = $objRIColumnID->getSequenceID();
            if($strSeqId != ""){
                $this->setSequenceID("J".$strSeqId);
            }
        }
    }
    //FixColumnイベント系----

    //----TableIUDイベント系
    
    /* start transaction 直後のselect .. for updateをskip
    function getSequencesForTrzStart(&$arySequence=array()){
        //----トランザクション内
        global $g;
        $intControlDebugLevel01=250;

        $boolRet = false;
        $intErrorType = null;
        $aryErrMsgBody = array();
        $strErrMsg = "";
        $strErrorBuf = "";

        $strFxName = __CLASS__."::".__FUNCTION__;
        dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-3",array(__FILE__,$strFxName)),$intControlDebugLevel01);
        try{
            if( strlen($this->getSequenceID())==0 ){
                //----シーケンスが設定されていない場合
                $intErrorType = 500;
                throw new Exception( '00000100-([CLASS]' . __CLASS__ . ',[FUNCTION]' . __FUNCTION__ . ')' );
                //シーケンスが設定されていない場合----
            }
            $arySequence[$this->getSequenceID().'_'] = $this->getSequenceID();
            $boolRet = true;
            //履歴シーケンスを捕まえる（デッドロック防止）----
        }
        catch(Exception $e){
            $tmpErrMsgBody = $e->getMessage();
            web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-5002",array($tmpErrMsgBody,$this->getSelfInfoForLog())));

            $strErrMsg = $g['objMTS']->getSomeMessage("ITAWDCH-ERR-15001",$this->getColLabel(true));
        }

        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
        dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-4",array(__FILE__,$strFxName)),$intControlDebugLevel01);
        return $retArray;
        //トランザクション内----
    }
    */
    /* journal tableへの 事前select .. for updateをskip
    public function inTrzBeforeTableIUDAction(&$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
        global $g;

        $intControlDebugLevel01=250;
        $strFxName = __CLASS__."::".__FUNCTION__;
        dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-3",array(__FILE__,$strFxName)),$intControlDebugLevel01);

        $retArray = array();
        $boolRet=false;
        $intErrorType=0;
        $strErrMsg="";
        try{
            if( strlen($this->getSequenceID())==0 ){
                //----シーケンスが設定されていない場合
                $intErrorType = 500;
                throw new Exception( '00000100-([CLASS]' . __CLASS__ . ',[FUNCTION]' . __FUNCTION__ . ')' );
                //シーケンスが設定されていない場合----
            }
            //----シーケンスが設定されている場合
            $retArray= getSequenceValue($this->getSequenceID(),true);

            if( $retArray[1] === 0 ){
                // JOURNAL専用のカラムなので、$reqOrgData、に代入してはならない
                $exeQueryData[$this->getID()] = array('JNL'=>$retArray[0]);
                $boolRet = true;
            }
            else{
                $intErrorType = 500;
                web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-5004",array($strFxName,$this->getSequenceID())));
                throw new Exception( '00000100-([CLASS]' . __CLASS__ . ',[FUNCTION]' . __FUNCTION__ . ')' );
            }
            //シーケンスが設定されている場合----
        }
        catch(Exception $e){
            $tmpErrMsgBody = $e->getMessage();
            web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-5002",array($tmpErrMsgBody,$this->getSelfInfoForLog())));

            $strErrMsg = $g['objMTS']->getSomeMessage("ITAWDCH-ERR-16001",$this->getColLabel(true));
        }
        
        $retArray[0] = $boolRet;
        $retArray[1] = $intErrorType;
        $retArray[2] = $strErrMsg;
        $retArray[3] = "";
        dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-4",array(__FILE__,$strFxName)),$intControlDebugLevel01);
        return $retArray;
    }
    */
    /* journal tableへのinsertをskip
    function inTrzAfterTableIUDAction(&$exeQueryData, &$reqOrgData=array(), &$aryVariant=array()){
        //----トランザクション内
        global $g;

        $intControlDebugLevel01 = 250;

        $boolRet = false;
        $intErrorType = null;
        $aryErrMsgBody = array();
        $strErrMsg = "";
        $strErrorBuf = "";
        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);

        $strFxName = __CLASS__."::".__FUNCTION__;
        dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-3",array(__FILE__,$strFxName)),$intControlDebugLevel01);
        try{
            $objTable = $this->objTable;
            $arrayObjColumn = $objTable->getColumns();
            $objTable->getRequiredUpdateDate4UColumnID();

            $exeJournalData = generateElementForJournalReg($exeQueryData,$aryVariant['edit_target_row'],$arrayObjColumn,$objTable->getRequiredUpdateDate4UColumnID(),$objTable->getDBMainTableHiddenID());

            $sqlJnlBody = generateJournalRegisterSQL($exeJournalData,$arrayObjColumn,$objTable->getDBJournalTableID(),$objTable->getDBJournalTableHiddenID() );
            
            $retSQLResultArray = singleSQLExecuteAgent($sqlJnlBody, $exeJournalData, $strFxName);
            if( $retSQLResultArray[0]===true ){
                $objQueryJnl =& $retSQLResultArray[1];
                $resultRowLength = $objQueryJnl->effectedRowCount();
                if( $resultRowLength!= 1 ){
                    $intErrorType = 500;
                    throw new Exception( '00000100-([CLASS]' . __CLASS__ . ',[FUNCTION]' . __FUNCTION__ . ')' );
                }
                $boolRet = true;
                unset($objQueryJnl);
            }
            else{
                $intErrorType = 500;
                throw new Exception( '00000200-([CLASS]' . __CLASS__ . ',[FUNCTION]' . __FUNCTION__ . ')' );
            }
        }
        catch(Exception $e){
            $tmpErrMsgBody = $e->getMessage();
            web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-5002",$tmpErrMsgBody));

            $strErrMsg = $g['objMTS']->getSomeMessage("ITAWDCH-ERR-16002",$this->getColLabel(true));
        }

        $retArray = array($boolRet,$intErrorType,$aryErrMsgBody,$strErrMsg,$strErrorBuf);
        dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-4",array(__FILE__,$strFxName)),$intControlDebugLevel01);
        return $retArray;
        //トランザクション内----
    }
    */
    //TableIUDイベント系----

    //ここまで継承メソッドの上書き処理----

    //----ここから新規メソッドの定義宣言処理

    //NEW[1]
    function setSequenceID($strSequenceId){
        $this->strSequenceId = $strSequenceId;
    }

    //NEW[2]
    function getSequenceID(){
        return $this->strSequenceId;
    }
    //ここまで新規メソッドの定義宣言処理----
}

