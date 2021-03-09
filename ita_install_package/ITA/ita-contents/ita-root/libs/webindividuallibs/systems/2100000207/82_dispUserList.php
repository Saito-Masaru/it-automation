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
    // ローカル変数宣言
    $intControlDebugLevel01 = 50;

    $strFxName = __FUNCTION__;
    dev_log($g['objMTS']->getSomeMessage("ITAWDCH-STD-1",__FILE__),$intControlDebugLevel01);

    $str_temp   = "";

	// DBアクセスを伴う処理開始
	try{
	    $objIntNumVali = new IntNumValidator(null,null,"","",array("NOT_NULL"=>true));
	    if( $objIntNumVali->isValid($p_role_id) === false ){
	        throw new Exception( '00000100-([FILE]' . __FILE__ . ',[LINE]' . __LINE__ . ')' );
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

        // ロール一覧(A_ROLE_LIST)が存在しているかチェック
        $sql = "SELECT DISUSE_FLAG
                FROM   A_ROLE_LIST
                WHERE  ROLE_ID = :ROLE_ID_BV
                AND DISUSE_FLAG IN ('0','1')";

        $tmpAryBind = array('ROLE_ID_BV'=>$p_role_id);
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

        $p_role_list_disuse_flag = $showTgtRow['DISUSE_FLAG'];

        // メンテナンスボタンの表示/非表示を切り替え
        if($p_role_list_disuse_flag === '0' ){
            $BG_COLOR = "";
        }
        else{
            $BG_COLOR = " class=\"disuse\" ";
        }

        // ロールアカウント紐付リスト(A_ROLE_ACCOUNT_LINK_LIST)を検索
        $sql = "SELECT TAB_1.USER_ID,
                        TAB_2.USERNAME,
                        TAB_2.USERNAME_JP,
                        TAB_2.DISUSE_FLAG,
                        TAB_2.ACCESS_AUTH
                FROM   A_ROLE_ACCOUNT_LINK_LIST TAB_1
                        LEFT JOIN A_ACCOUNT_LIST TAB_2 ON (TAB_1.USER_ID = TAB_2.USER_ID)
                WHERE  TAB_1.ROLE_ID = :ROLE_ID_BV
                AND    TAB_1.DISUSE_FLAG = '0'
                ORDER BY TAB_2.USERNAME ASC ";

        $tmpAryBind = array('ROLE_ID_BV'=>$p_role_id);
        $retArray = singleSQLExecuteAgent($sql, $tmpAryBind, $strFxName);
        if( $retArray[0] === true ){
            $objQuery =& $retArray[1];
            $str_temp =
<<< EOD
                <div class="fakeContainer_Yobi1">
                <table id="DbTable_Yobi1">
                    <tr class="defaultExplainRow">
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 0, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1069055")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 1, nsort);"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1070101")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 2       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1070201")}</span></th>
                        <th scope="col" onClick="tableSort(1, this, 'DbTable_Yobi1_data', 3       );"  class="sort" ><span class="generalBold">{$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1070401")}</span></th>
                    </tr>
EOD;
            $output_str .= $str_temp;
            $num_rows = 0;
            $temp_no = 1;
            while ( $user_row = $objQuery->resultFetch() ){
                $num_rows += 1;
                // 項目生成
                $user_role_id = explode("," , $user_row['ACCESS_AUTH']);
                $COLUMN_00 = $temp_no;
                $COLUMN_01 = nl2br(htmlspecialchars($user_row['USER_ID']));
                $COLUMN_02 = nl2br(htmlspecialchars($user_row['USERNAME']));
                $COLUMN_03 = nl2br(htmlspecialchars($user_row['USERNAME_JP']));
                $url = "01_browse.php?no=2100000208&filter=on&Filter1Tbl_1__S=" .str_replace(" ","%20",$COLUMN_01) ."&Filter1Tbl_1__E=" .str_replace(" ","%20",$COLUMN_01);
                $url_02 = "01_browse.php?no=2100000208&filter=on&Filter1Tbl_2=" .str_replace(" ","%20",$COLUMN_02);
                $htmlText = "<td class=\"number\" " .$BG_COLOR. "><a href=\"" .$url. "\"target=\"_blank\">" .$COLUMN_01. "</a></td>";
                $htmlText_02 = "<td" .$BG_COLOR. "><a href=\"" .$url_02. "\"target=\"_blank\">" .$COLUMN_02. "</a></td>";
                // アクセス許可ロール判定
                $auth_flag = '0';
                foreach ($user_role_id as $value) {
                  if(in_array($value, $role_id)){
                    $auth_flag = '1';
                  }
                  if(empty($value)){
                    $auth_flag = '1';
                  }
                }
                if($auth_flag == '0'){
                  $COLUMN_01 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($user_row['USER_ID'])) . ')';
                  $COLUMN_02 = $g['objMTS']->getSomeMessage("ITAWDCH-STD-11101") . '(' . nl2br(htmlspecialchars($user_row['USER_ID'])) . ')';
                  $htmlText = "<td" .$BG_COLOR .">" .$COLUMN_01. "</td>";
                  $htmlText_02 = "<td" .$BG_COLOR .">" .$COLUMN_02. "</td>";
                }


                $str_temp =
<<< EOD
                    <tr valign="top">
                        <td class="likeHeader number" scope="row" >{$COLUMN_00}</td>
                        {$htmlText}
                        {$htmlText_02}
                        <td{$BG_COLOR}>{$COLUMN_03}</td>
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
                $output_str = $g['objMTS']->getSomeMessage("ITAWDCH-MNU-1069054");
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
