<?php
/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2017 Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Prevent direct access
defined('_JEXEC') or die;

$layoutPath = JPluginHelper::getLayoutPath('loginguard', 'u2f', 'error');
include $layoutPath;

?>
<div id="loginguard-u2f-controls">
    <input class="form-control" id="loginguard-method-code" name="code" value="" placeholder="" type="hidden">

    <div class="control-group">
        <div class="controls">
            <a class="btn btn-primary btn-large btn-lg"
               onclick="akeeba.LoginGuard.u2f.setUp();">
                <span class="icon icon-lock glyphicon glyphicon-lock"></span>
				<?php echo JText::_('PLG_LOGINGUARD_U2F_LBL_REGISTERKEY'); ?>
            </a>
        </div>
    </div>
</div>