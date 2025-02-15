<?php
/**
 * The ajaxgetdropmenu view file of market module of ZenTaoPMS.
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Hu Fangzhou <hufangzhou@easycorp.ltd>
 * @package     market
 * @link        https://www.zentao.net
 */
?>

<?php js::set('marketID', $marketID);?>
<?php js::set('module', $module);?>
<?php js::set('method', $method);?>
<?php
$currentMarket = '';

foreach($markets as $market) $marketNames[] = $market->name;

$marketsPinYin = common::convert2Pinyin($marketNames);

$marketHtml = '<ul class="tree">';
foreach($markets as $market)
{
    $icon = '';
    $marketName  = $icon . $market->name;
    $selected    = $market->id == $marketID ? 'selected' : '';
    $marketHtml .= '<li>' . html::a(sprintf($link, $market->id), $marketName, '', "class='$selected clickable' title='{$market->name}' data-key='" . zget($marketsPinYin, $market->name, '') . "'") . '</li>';
}
$marketHtml .= '</ul>';
?>

<div class="table-row"><?php echo $marketHtml;?> </div>
