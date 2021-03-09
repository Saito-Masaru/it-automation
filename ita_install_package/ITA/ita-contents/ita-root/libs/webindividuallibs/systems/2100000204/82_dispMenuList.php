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

    $intControlDebugLevel01 = 50;

    $strFxName = __FUNCTION__;
    dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-1",__FILE__),$intControlDebugLevel01);

    // ローカル変数宣言
    $str_temp   = "";

    // DBアクセスを伴う処理開始
    try{
        $objIntNumVali = new IntNumValidator(null,null,"","",array("NOT_NULL"=>true));
        if( $objIntNumVali->isValid($p_menu_group_id) === false ){
            throw new Exception( '00000100-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }

        // メニューグループ一覧(A_MENU_GROUP_LIST)が存在しているかチェック
        $sql = "SELECT DISUSE_FLAG
                FROM   A_MENU_GROUP_LIST
                WHERE  MENU_GROUP_ID = :MENU_GROUP_ID_BV ";

        $tmpAryBind = array('MENU_GROUP_ID_BV'=>$p_menu_group_id);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        if( $retArray[0] === true ){
            $intTmpRowCount=0;
            $showTgtRow = array();
            $objQuery =& $retArray[1];
            while($row = $objQuery->resultFetch() ){
                if($row !== false){
                    $intTmpRowCount+=1;
                }
                if($intTmpRowCount==1){
                    $showTgtRow = $row;
                }
            }
            $selectRowLength = $intTmpRowCount;
            if( $selectRowLength != 1 ){
                throw new Exception( '00000200-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
            }
            unset($objQuery);
        }
        else{
            throw new Exception( '00000300-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }

        $p_menu_group_list_disuse_flag = $showTgtRow['DISUSE_FLAG'];

        if($p_menu_group_list_disuse_flag === '0' ){
            $BG_COLOR = "";
        }
        else{
            $BG_COLOR = " class=\"disuse\" ";
        }

        // ログイン中のユーザのロールID取得
        $sql = "SELECT ROLE_ID
                FROM   D_ROLE_ACCOUNT_LINK_LIST
                WHERE  USER_ID = :USER_ID
                AND DISUSE_FLAG = '0'";

        $tmpAryBind = array('USER_ID'=>$g['login_id']);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        $role_id = array();
        if( $retArray[0] === true ){
            $objQuery =& $retArray[1];
            while($row = $objQuery->resultFetch() ){
                array_push($role_id, $row['ROLE_ID']);
            }
            unset($objQuery);
        }
        else{
            throw new Exception( '00000300-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }

        // メニューグループアメニュー紐付リスト(A_ROLE_MENU_LINK_LIST)を検索

        $sql = "SELECT TAB_1.MENU_ID,
                        TAB_1.MENU_NAME,
                        TAB_2.NAME AS LOGIN_NECESSITY_DISP,
                        TAB_1.DISP_SEQ,
                        TAB_1.ACCESS_AUTH
                FROM   A_MENU_LIST TAB_1
                        LEFT JOIN A_LOGIN_NECESSITY_LIST TAB_2 ON (TAB_1.LOGIN_NECESSITY = TAB_2.FLAG )
                WHERE  TAB_1.MENU_GROUP_ID = :MENU_GROUP_ID_BV
                AND    TAB_1.DISUSE_FLAG = '0'
                ORDER BY TAB_1.DISP_SEQ ASC ";

        $tmpAryBind = array('MENU_GROUP_ID_BV'=>$p_menu_group_id);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        if( $retArray[0] === true ){
            $objQuery =& $retArray[1];
            $str_temp =
<<< EOD
                <div class="fakeContainer_Yobi1">
                <table id="DbTable_Yobi1">
                    <tr class="defaultExplainRow">
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 0, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1030082")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 1, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040901")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 2, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040101")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 3       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040601")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 4       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1040701")}</span></th>
                    </tr>
EOD;

            $output_str .= $str_temp;

            $temp_no = 1;
            $num_rows = 0;
            while ( $menu_row =  $objQuery->resultFetch() ){
                $num_rows += 1;
                // 項目生成
                $menu_role_id = explode("," , $menu_row['ACCESS_AUTH']);
                $COLUMN_00 = $temp_no;
                $COLUMN_01 = nl2br(htmlspecialchars($menu_row['DISP_SEQ']));
                $COLUMN_02 = nl2br(htmlspecialchars($menu_row['MENU_ID']));
                $COLUMN_03 = nl2br(htmlspecialchars($menu_row['MENU_NAME']));
                $COLUMN_04 = nl2br(htmlspecialchars($menu_row['LOGIN_NECESSITY_DISP']));
                $url = "01_browse.php?no=2100000205&filter=on&Filter1Tbl_1__S=" .str_replace(" ","%20",$COLUMN_02) ."&Filter1Tbl_1__E=" .str_replace(" ","%20",$COLUMN_02);
                $url_02 = "01_browse.php?no=2100000205&filter=on&Filter1Tbl_4=" .str_replace(" ","%20",$COLUMN_03);
                $htmlText = "<td" .$BG_COLOR. "><a href=\"" .$url. "\"target=\"_blank\">" .$COLUMN_02. "</a></td>";
                $htmlText_02 = "<td" .$BG_COLOR. "><a href=\"" .$url_02. "\"target=\"_blank\">" .$COLUMN_03. "</a></td>";
                // アクセス許可ロール判定
                $auth_flag = '0';
                foreach ($menu_role_id as $value) {
                  if(in_array($value, $role_id)){
                    $auth_flag = '1';
                  }
                  if(empty($value)){
                    $auth_flag = '1';
                  }
                }
                if($auth_flag == '0'){
                  $COLUMN_02 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_ID'])) . ')';
                  $COLUMN_03 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($menu_row['MENU_ID'])) . ')';
                  $htmlText = "<td" .$BG_COLOR .">" .$COLUMN_02. "</td>";
                  $htmlText_02 = "<td" .$BG_COLOR .">" .$COLUMN_03. "</td>";
                }

                $str_temp =
<<< EOD
                    <tr valign="top">
                        <td class="likeHeader number" scope="row" >{$COLUMN_00}</td>
                        <td class="number" {$BG_COLOR}>{$COLUMN_01}</td>
                        {$htmlText}
                        {$htmlText_02}
                        <td{$BG_COLOR}>{$COLUMN_04}</td>
                    </tr>
EOD;

                $output_str .= $str_temp;
                $temp_no++;
            }
            unset($objQuery);

            $str_temp =
<<< EOD
                </table>
                </div>
EOD;
            $output_str .= $str_temp;

            if( $num_rows < 1 ){
                $output_str = $g['objMTS']->getSomeMessage("ITAWDCH-MNU-1030083");
            }
        }
        else{
            throw new Exception( '00000400-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
        }
    }
    catch (Exception $e){
        // エラーフラグをON
        $error_flag = 1;

        $tmpErrMsgBody = $e->getMessage();
        dev_log($tmpErrMsgBody, $intControlDebugLevel01);

        // DBアクセス事後処理
        if ( isset($objQuery) )    unset($objQuery);

        web_log($g['objMTS']->getSomeMessage("ITAWDCH-ERR-4011",array($strFxName,$tmpErrMsgBody)));
    }

    dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-2",__FILE__),$intControlDebugLevel01);
?>
