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
//    ・独自シーケンスの管理を行う
//
//////////////////////////////////////////////////////////////////////

$tmpFx = function (&$aryVariant=array(),&$arySetting=array()){
    global $g;

    $arrayWebSetting = array();
    $arrayWebSetting['page_info'] = $g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230001");

    // 項番
    $table = new TableControlAgent('D_SEQUENCE', 'ID',  $g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230011"), 'D_SEQUENCE_JNL');
    $table->setDBMainTableLabel($g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230011"));

    // TABLE settings
    $table->setGeneObject('AutoSearchStart',true);  //('',true,false)
    $table->setGeneObject('webSetting', $arrayWebSetting);
    $table->setDBMainTableHiddenID('A_SEQUENCE');
    $table->setDBJournalTableHiddenID('A_SEQUENCE_JNL');

    // シーケンス名
    $c = new TextColumn('NAME',$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230021"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230022"));
    $c->setRequired(true);
    $c->setValidator(new TextValidator(0, 64, false));
    $c->setHiddenMainTableColumn(true);
    $table->addColumn($c);

    // 設定値
    $c = new TextColumn('VALUE',$g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230031"));
    $c->setDescription($g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230032"));
    $c->setRequired(true);
    $c->setValidator(new IntNumValidator(-2147483648, 2147483647, false));
    $c->setHiddenMainTableColumn(true);
    $table->addColumn($c);

    // MENU_ID
    $c = new IDColumn('MENU_ID', $g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230041"), 'D_MENU_LIST', 'MENU_ID', 'MENU_PULLDOWN', NULL);
    $c->setRequired(false);
    $c->setDescription($g['objMTS']->getSomeMessage("ITAWDCH-MNU-1230042"));
    $c->setHiddenMainTableColumn(true);
    $table->addColumn($c);

    $table->fixColumn();

    // SEQENCE settings(このloadTableでの管理用)
    $tmpAryColumn = $table->getColumns();
    $tmpAryColumn['ID']->setSequenceID('SEQ_A_SEQUENCE');
    $tmpAryColumn['JOURNAL_SEQ_NO']->setSequenceID('JSEQ_A_SEQUENCE');

    // 廃止フラグ非表示(filter条件含め)
    $tmpAryColumn['DISUSE_FLAG']->getOutputType('filter_table')->setVisible(false);
    $tmpAryColumn['DISUSE_FLAG']->getOutputType('print_table')->setVisible(false);
    $tmpAryColumn['DISUSE_FLAG']->getOutputType('update_table')->setVisible(false);
    $tmpAryColumn['DISUSE_FLAG']->getOutputType('register_table')->setVisible(false);
    $tmpAryColumn['DISUSE_FLAG']->getOutputType('excel')->setVisible(false);

    // 最終更新日時非表示(filter条件含め)
    $tmpAryColumn['LAST_UPDATE_TIMESTAMP']->getOutputType('filter_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_TIMESTAMP']->getOutputType('print_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_TIMESTAMP']->getOutputType('update_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_TIMESTAMP']->getOutputType('register_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_TIMESTAMP']->getOutputType('excel')->setVisible(false);

    // 最終更新者非表示(filter条件含め)
    $tmpAryColumn['LAST_UPDATE_USER']->getOutputType('filter_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_USER']->getOutputType('print_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_USER']->getOutputType('update_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_USER']->getOutputType('register_table')->setVisible(false);
    $tmpAryColumn['LAST_UPDATE_USER']->getOutputType('excel')->setVisible(false);

    return $table;
};
loadTableFunctionAdd($tmpFx,__FILE__);
unset($tmpFx);
?>
