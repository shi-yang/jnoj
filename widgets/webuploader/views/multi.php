<div id="multi-container">
    <div id="uploader">
        <div class="queueList">
            <div id="dndArea" class="placeholder">
                <div id="filePicker"></div>
                <p><?= Yii::t('webuploader', 'Or Drag files here, a single optional up to 300') ?></p>
            </div>
        </div>
        <div class="statusBar" style="display:none;">
            <div class="progress">
                <span class="text">0%</span>
                <span class="percentage"></span>
            </div><div class="info"></div>
            <div class="btns">
                <div id="filePicker2"></div><div class="uploadBtn"><?= Yii::t('webuploader', 'Start upload') ?></div>
            </div>
        </div>
    </div>
</div>
