<?php
$lang->action->objectTypes['demand']         = '需求池需求';
$lang->action->objectTypes['demandpool']     = '需求池';
$lang->action->objectTypes['charter']        = '立項';
$lang->action->objectTypes['roadmap']        = '路標';
$lang->action->objectTypes['market']         = '市場';
$lang->action->objectTypes['marketreport']   = '報告';
$lang->action->objectTypes['marketresearch'] = '調研';
$lang->action->objectTypes['researchstage']  = '調研階段';

$lang->action->label->demandpool     = '需求池|demandpool|view|id=%s';
$lang->action->label->demand         = '需求|demand|view|id=%s';
$lang->action->label->charter        = '立項|charter|view|id=%s';
$lang->action->label->roadmap        = '路標|roadmap|view|id=%s';
$lang->action->label->market         = '市場|market|view|id=%s';
$lang->action->label->marketreport   = '市場報告|marketreport|view|id=%s';
$lang->action->label->marketresearch = '市場調研|marketresearch|stage|id=%s';

$lang->action->label->deletechildrendemand = "刪除了子需求";

$lang->action->desc->createchildrendemand = '$date, 由 <strong>$actor</strong> 創建子需求 <strong>$extra</strong>。' . "\n";
$lang->action->desc->deletechildrendemand = '$date, 由 <strong>$actor</strong> 刪除子需求<strong>$extra</strong>。' . "\n";

$lang->action->label->distributed               = '分發了';
$lang->action->label->reviewreverted            = '評審了';
$lang->action->label->createchildrendemand      = '創建了子需求';
$lang->action->label->reviewbycharter           = "修改評審結果";
$lang->action->label->linked2roadmap            = "關聯了路標";
$lang->action->label->unlinkedfromroadmap       = "移除了路標";
$lang->action->label->changedbycharter          = "變更了路標";
$lang->action->label->linkur                    = '關聯用戶需求到了';
$lang->action->label->unlinkur                  = '移除了用戶需求從';
$lang->action->label->linked2charter            = '關聯了立項';
$lang->action->label->managedistributedproducts = '維護了';

$lang->action->dynamicAction->story['linked2roadmap']      = "{$lang->URCommon}關聯路標";
$lang->action->dynamicAction->story['unlinkedfromroadmap'] = "計劃移除{$lang->URCommon}";

$lang->action->dynamicAction->roadmap['linkur']   = "關聯需求";
$lang->action->dynamicAction->roadmap['unlinkur'] = "移除需求";

$lang->action->search->label['linked2roadmap']      = $lang->action->label->linked2roadmap;
$lang->action->search->label['unlinkedfromroadmap'] = $lang->action->label->unlinkedfromroadmap;

$lang->action->desc->published                 = '$date, 由 <strong>$actor</strong> 發佈。' . "\n";
$lang->action->desc->managedistributedproducts = '$date, 由 <strong>$actor</strong> 維護分發產品。分發產品為 <strong>$extra</strong>。' . "\n";
