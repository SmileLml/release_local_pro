<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php
js::set('pleaseInput', $lang->ai->miniPrograms->placeholder->input);
js::set('deleteTip', $lang->ai->miniPrograms->deleteFieldTip);
js::set('emptyWarning', $lang->ai->miniPrograms->field->emptyNameWarning);
js::set('duplicatedWarning', $lang->ai->miniPrograms->field->duplicatedNameWarning);
js::set('emptyOptionWarning', $lang->ai->miniPrograms->field->emptyOptionWarning);
js::set('appID', $appID);
js::set('publishConfirm', $lang->ai->miniPrograms->publishConfirm);
js::set('emptyPrompterTip', $lang->ai->miniPrograms->emptyPrompterTip);
js::set('currentFields', $currentFields);
js::set('currentPrompt', $currentPrompt);
js::set('promptPlaceholder', $lang->ai->miniPrograms->placeholder->prompt);
js::set('fieldName', $lang->ai->miniPrograms->field->name);
?>
<?php js::import($jsRoot . 'textcomplete/jquery.textcomplete.min.js'); ?>

<template id="option-template">
  <div class="input-group">
    <span class="input-group-addon"><?= $lang->ai->miniPrograms->field->option; ?>1</span>
    <input name="option[]" type="text" class="form-control" placeholder="<?= $lang->ai->miniPrograms->placeholder->input; ?>" />
    <span class="input-group-btn">
      <button type="button" class="btn btn-default btn-icon" onclick="addFieldOption(event)"><i class="icon icon-plus"></i></button>
      <button type="button" class="btn btn-default btn-icon" onclick="removeFieldOption(event)"><i class="icon icon-minus"></i></button>
    </span>
  </div>
</template>

<template id="field-template">
  <form>
    <table class="table table-form">
      <tbody>
        <tr>
          <th><?= $lang->ai->miniPrograms->field->name; ?></th>
          <td class="required"><input type="text" name="field-name" maxlength="16" class="form-control" /></td>
        </tr>
        <tr>
          <th><?= $lang->ai->miniPrograms->field->type; ?></th>
          <td>
            <select name="field-type" class="form-control" onchange="changeFieldType(event)">
              <option value="text"><?= $lang->ai->miniPrograms->field->typeList['text']; ?></option>
              <option value="textarea"><?= $lang->ai->miniPrograms->field->typeList['textarea']; ?></option>
              <option value="radio"><?= $lang->ai->miniPrograms->field->typeList['radio']; ?></option>
              <option value="checkbox"><?= $lang->ai->miniPrograms->field->typeList['checkbox']; ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <th><?= $lang->ai->miniPrograms->field->placeholder; ?></th>
          <td><input name="placeholder" type="text" class="form-control" placeholder="<?= $lang->ai->miniPrograms->placeholder->default; ?>" /></td>
        </tr>
        <tr class="field-options hidden">
          <th></th>
          <td></td>
        </tr>
        <tr>
          <th><?= $lang->ai->miniPrograms->field->required; ?></th>
          <td>
            <div class="radio" style="display: flex; align-items: center; gap: 4px;">
              <label>
                <input type="radio" name="field-required" value="1" checked><?= $lang->ai->miniPrograms->field->requiredOptions[1]; ?>
              </label>
              <label>
                <input type="radio" name="field-required" value="0"><?= $lang->ai->miniPrograms->field->requiredOptions[0]; ?>
              </label>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</template>

<div class="modal fade" id="add-field-modal">
  <div class="modal-dialog" style="width: 600px;">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: none; padding-left: 12px;">
        <strong style="font-size: 20px;"><?= $lang->ai->miniPrograms->field->add; ?></strong>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body" style="display: flex; gap: 42px; padding-right: 36px;"></div>
      <div class="modal-footer" style="display: flex; justify-content: center; border-top: none;">
        <button type="button" class="btn btn-wide btn-primary" onclick="saveNewField()"><?= $lang->save; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="edit-field-modal">
  <div class="modal-dialog" style="width: 600px;">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: none; padding-left: 12px;">
        <strong style="font-size: 20px;"><?= $lang->ai->miniPrograms->field->edit; ?></strong>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body" style="display: flex; gap: 42px; padding-right: 36px;"></div>
      <div class="modal-footer" style="display: flex; justify-content: center; border-top: none;">
        <button type="button" class="btn btn-wide btn-primary" onclick="saveEditedField()"><?= $lang->save; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="back-to-list-modal">
  <div class="modal-dialog" style="width: 480px;">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: none; padding-left: 12px; display: flex; align-items: center; gap: 16px; margin: 0;">
        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12.0159" cy="12.0163" r="12" transform="rotate(0.0777774 12.0159 12.0163)" fill="#FFA34D" />
          <path d="M12.4516 14.621C12.8867 14.6215 13.3224 14.1498 13.3231 13.6775L13.6588 7.42006C13.6595 6.94777 13.661 6.00319 12.3559 6.0016C11.1595 6.00013 11.0495 6.8265 11.0486 7.41686L11.3655 13.6751C11.5823 14.1476 12.0166 14.6204 12.4516 14.621ZM12.4499 15.8017C11.7973 15.8009 11.1439 16.3905 11.1426 17.217C11.1416 17.9254 11.6843 18.6345 12.4456 18.6354C13.2069 18.6363 13.7516 18.0467 13.7528 17.2202C13.7541 16.3936 13.1024 15.8025 12.4499 15.8017Z" fill="white" />
        </svg>
        <span style="font-size: 16px;"><?= $lang->ai->miniPrograms->backToListPageTip; ?></span>
      </div>
      <div class="modal-footer" style="display: flex; padding-top: 0; justify-content: center; border-top: none; gap: 10px;">
        <button class="btn btn-wide btn-primary" onclick="backWithSave()"><?= $lang->save; ?></button>
        <button class="btn btn-wide" data-dismiss="modal"><?= $lang->cancel; ?></button>
        <button class="btn btn-wide btn-link" style="color: var(--color-primary);" onclick="backWithoutSave()"><?= $lang->ai->prompts->roleTemplateSaveList['discard']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="publish-confirm-modal">
  <div class="modal-dialog" style="width: 480px;">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: none; padding-left: 12px; display: flex; align-items: center; gap: 16px; margin: 0;">
        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12.0159" cy="12.0163" r="12" transform="rotate(0.0777774 12.0159 12.0163)" fill="#FFA34D" />
          <path d="M12.4516 14.621C12.8867 14.6215 13.3224 14.1498 13.3231 13.6775L13.6588 7.42006C13.6595 6.94777 13.661 6.00319 12.3559 6.0016C11.1595 6.00013 11.0495 6.8265 11.0486 7.41686L11.3655 13.6751C11.5823 14.1476 12.0166 14.6204 12.4516 14.621ZM12.4499 15.8017C11.7973 15.8009 11.1439 16.3905 11.1426 17.217C11.1416 17.9254 11.6843 18.6345 12.4456 18.6354C13.2069 18.6363 13.7516 18.0467 13.7528 17.2202C13.7541 16.3936 13.1024 15.8025 12.4499 15.8017Z" fill="white" />
        </svg>
        <strong style="font-size: 16px;"><?= $lang->ai->miniPrograms->publishConfirm[0]; ?></strong>
      </div>
      <div class="modal-body" style="padding-left: 70px;"><?= $lang->ai->miniPrograms->publishConfirm[1]; ?></div>
      <div class="modal-footer" style="display: flex; justify-content: center; border-top: none; gap: 10px;">
        <a class="btn btn-wide btn-primary"><?= $lang->confirm; ?></a>
        <a class="btn btn-wide" data-dismiss="modal"><?= $lang->cancel; ?></a>
      </div>
    </div>
  </div>
</div>
<div id="mainContent" class="main-content">
  <div style="flex-basis: 30%; margin-right: 16px;">
    <header>
      <strong><?= $lang->ai->miniPrograms->field->configuration; ?></strong>
    </header>
    <main class="field-configuration-main">
      <div>
        <a onclick="toAddField()" style="border: 1px dashed #D8DBDE; border-radius: 2px; display: flex; align-items: center; flex-direction: column; padding: 12px; gap: 4px;">
          <div style="color: #2E7FFF; display: flex; align-items: center; gap: 4px;"><i class="icon icon-plus"></i><span><?= $lang->ai->miniPrograms->field->add; ?></span></div>
          <div style="color: #9EA3B0; font-size: 12divx;"><?= $lang->ai->miniPrograms->field->addTip; ?></div>
        </a>
      </div>
      <table class="table table-form">
        <tbody class="field-configuration" id="sortable-list"></tbody>
      </table>
    </main>
  </div>
  <div style="flex-basis: 40%; border-right: 1px solid #E6EAF1;">
    <header>
      <strong><?= $lang->ai->miniPrograms->field->debug; ?></strong>
    </header>
    <main>
      <div class="content-debug-area" style="min-height: 50%;">
        <div class="area-title">
          <strong><?= $lang->ai->miniPrograms->field->contentDebugging; ?></strong>
          <i title="<?= $lang->help; ?>" class="icon icon-help text-warning"></i>
          <span class="text-muted"><?= $lang->ai->miniPrograms->field->contentDebuggingTip; ?></span>
        </div>
        <table class="table table-form">
          <tbody class="field-content"></tbody>
        </table>
      </div>
      <div class="prompt-design-area" style="height: 50%; position: relative; padding-top: 0;">
        <div class="area-title">
          <strong><?= $lang->ai->miniPrograms->field->prompterDesign; ?></strong>
          <i title="<?= $lang->help; ?>" class="icon icon-help text-warning"></i>
          <span class="text-muted"><?= $lang->ai->miniPrograms->field->prompterDesignTip; ?></span>
        </div>
        <div class="form-control" id="autocomplete-textarea" contenteditable="true" style="overflow-wrap: break-word; overflow-y: auto; position: absolute; top: 32px; left: 24px; right: 24px; bottom: 24px; height: auto; width: auto;"></div>
      </div>
    </main>
  </div>
  <div style="flex-basis: 30%;">
    <header>
      <strong><?= $lang->ai->miniPrograms->field->preview; ?></strong>
    </header>
    <main>
      <div class="prompt-preview-area" style="height: 50%; position: relative;">
        <div class="area-title" style="display: flex; justify-content: space-between;">
          <strong><?= $lang->ai->miniPrograms->field->prompterPreview; ?></strong>
          <button class="btn btn-link" style="color: #2E7FFF; position: absolute; right: 16px;" id="generate-result">
            <i class="icon-publish text-primary"></i>
            <?= $lang->ai->miniPrograms->field->generateResult; ?>
          </button>
        </div>
        <div class="preview-container"></div>
      </div>
      <div class="prompt-result-area" style="height: 50%; position: relative; padding-top: 0;">
        <div class="area-title">
          <strong><?= $lang->ai->miniPrograms->field->resultPreview; ?></strong>
        </div>
        <div class="preview-container"></div>
      </div>
    </main>
  </div>
</div>
<footer style="display: flex; justify-content: center; align-items: center; height: 56px; background: #fff; border-top: 1px solid #eff1f7; position: fixed; bottom: 0; left: 20px; right: 20px; gap: 24px;">
  <a onclick="backToList()" class="btn btn-wide"><?= $lang->ai->miniPrograms->backToListPage; ?></a>
  <a href="<?= $this->createLink('ai', 'editMiniProgram', "appID=$appID"); ?>" class="btn btn-wide"><?= $lang->ai->miniPrograms->lastStep; ?></a>
  <a class="btn btn-wide btn-secondary" onclick="saveMiniProgram('0')"><?= $lang->save; ?></a>
  <a class="btn btn-wide btn-primary" onclick="saveMiniProgram('1')"><?= $lang->ai->prompts->action->publish; ?></a>
</footer>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
