<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

// retrieve any state parameters
if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('DeptIdxOrderDir') ? ($AppUI->getState('DeptIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('DeptIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('DeptIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('DeptIdxOrderBy') ? $AppUI->getState('DeptIdxOrderBy') : 'dept_name';
$orderdir = $AppUI->getState('DeptIdxOrderDir') ? $AppUI->getState('DeptIdxOrderDir') : 'asc';

if (isset($_REQUEST['owner_filter_id'])) {
	$AppUI->setState('dept_owner_filter_id', w2PgetParam($_REQUEST, 'owner_filter_id', null));
	$owner_filter_id = w2PgetParam($_REQUEST, 'owner_filter_id', null);
} else {
	$owner_filter_id = $AppUI->getState('dept_owner_filter_id');
	if (!isset($owner_filter_id)) {
		$owner_filter_id = 0; //By default show all companies instead of $AppUI->user_id current user.
		$AppUI->setState('dept_owner_filter_id', $owner_filter_id);
	}
}

// get any records denied from viewing
$obj = new CDepartment();
$deny = $obj->getDeniedRecords($AppUI->user_id);

// Company search by Kist
$search_string = w2PgetParam($_REQUEST, 'search_string', '');
if ($search_string != '') {
	$search_string = $search_string == '-1' ? '' : $search_string;
	$AppUI->setState('dept_search_string', $search_string);
} else {
	$search_string = $AppUI->getState('dept_search_string');
}

$search_string = w2PformSafe($search_string, true);

$perms = &$AppUI->acl();
$owner_list = array(0 => $AppUI->_('All', UI_OUTPUT_RAW)) + $perms->getPermittedUsers('departments');
$owner_combo = arraySelect($owner_list, 'owner_filter_id', 'class="text" onchange="javascript:document.searchform.submit()"', $owner_filter_id, false);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Departments', 'departments.png', $m, $m . '.' . $a);
//TODO: search box doesn't work..
//$titleBlock->addCell('<form name="searchform" action="?m=departments" method="post" accept-charset="utf-8">
//                    <input type="text" class="text" name="search_string" value="' . $search_string . '" /></form>');
//$titleBlock->addCell($AppUI->_('Search') . ':');

$titleBlock->addCell('<form name="searchform2" action="?m=departments" method="post" accept-charset="utf-8">' .
        arraySelect($owner_list, 'owner_filter_id', 'onChange="document.searchform2.submit()" size="1" class="text"', $owner_filter_id) .
        '</form>');
$titleBlock->addCell($AppUI->_('Owner filter') . ':');
$titleBlock->show();

if (isset($_GET['tab'])) {
	$AppUI->setState('DeptIdxTab', w2PgetParam($_GET, 'tab', null));
}
$deptsTypeTab = defVal($AppUI->getState('DeptIdxTab'), 0);
$deptsType = $deptsTypeTab;

// load the department types
$deptTypes = w2PgetSysVal('DepartmentType');

$tabBox = new CTabBox('?m=departments', W2P_BASE_DIR . '/modules/departments/', $deptsTypeTab);
if ($tabBox->isTabbed()) {
	array_unshift($deptTypes, $AppUI->_('All Departments', UI_OUTPUT_RAW));	
}

// tabbed information boxes
foreach ($deptTypes as $deptType) {
	$tabBox->add('vw_depts', $deptType);
}
$tabBox->show();