<?php
/**
 * https://docs.dhtmlx.com/gantt/api__gantt_autosize_config.html
 *
 * @var array $database
 * @var string $pluginName
 */

use \dokuwiki\plugin\yuriigantt\src\Driver\Embedded as EmbeddedDriver;

?>
<link rel="stylesheet" href="<?= DOKU_URL ?>lib/plugins/<?= $pluginName; ?>/3rd/dhtmlxgantt/dhtmlxgantt.css?v=6.3.5">
<script src="<?= DOKU_URL ?>lib/plugins/<?= $pluginName; ?>/3rd/dhtmlxgantt/dhtmlxgantt.js?v=6.3.5"></script>
<script src="<?= DOKU_URL ?>lib/plugins/<?= $pluginName; ?>/3rd/dhtmlxgantt/ext/dhtmlxgantt_fullscreen.js?v=6.3.5"></script>
<?php
$lang = $GLOBALS['conf']['lang'];
$lang = preg_replace("/[^a-z]+/", "", $lang);
$lang = $lang === 'uk' ? 'ua' : $lang;
$base = "/3rd/dhtmlxgantt/locale/locale_$lang.js";
?>
<?php
$filename = dirname(__DIR__, 2) . $base;
if (file_exists($filename)): ?>
<script src="<?= DOKU_URL ?>lib/plugins/<?= $pluginName; ?><?=$base?>?v=6.3.5"></script>
<?php endif; ?>
<input id="fullscreen_button" type="button" value="Toggle Fullscreen"/>
<br/><br/>
<div id="<?= $pluginName; ?>"></div>
<script>
    let database = <?= json_encode($database); ?>;

    gantt.config.autosize = "y"
    gantt.config.date_format = "%d-%m-%Y %H:%i"
    gantt.config.order_branch = true
    gantt.config.order_branch_free = true
    gantt.init('<?=$pluginName;?>')

    if (database.dsn === '<?= EmbeddedDriver::DSN ?>') {
        gantt.config.cascade_delete = false; // optimization
        gantt.parse(database.gantt)
    }

    let dp = gantt.createDataProcessor({
        task: {
            create: function (data) {
                restCall('create', 'task', data)
            },
            update: function (data, id) {
                restCall('update', 'task', data, id)
            },
            delete: function (id) {
                restCall('delete', 'task', null, id)
            }
        },
        link: {
            create: function (data) {
                restCall('create', 'link', data)
            },
            update: function (data, id) {
                restCall('update', 'link', data, id)
            },
            delete: function (id) {
                restCall('delete', 'link', null, id)
            }
        }
    });
    dp.attachEvent("onAfterUpdate", function(id, action, tid, response){
        if(action === 'error'){
            console.warn('ERROR', response)
        }
    });

    function restCall(action, entity, data, id) {
        gantt.ajax.post('<?= DOKU_URL . 'lib/exe/ajax.php'; ?>', {
            call: 'plugin_<?=$pluginName;?>',
            payload: {
                pageId: database.pageId,
                version: database.version,
                action: action,
                entity: entity,
                data: data,
                id: id,
                test: true
            }
        }).then(function(response){
            var res = JSON.parse(response.responseText);
            console.log(res)
            if (res && res.status == "ok") {
                // response is ok
                console.log(res)
            }
        })
    }
</script>
<script>
    let button = document.getElementById("fullscreen_button");
    button.addEventListener("click", function(){
        if (!gantt.getState().fullscreen) {
            // expanding the gantt to full screen
            gantt.expand();
        }
        else {
            // collapsing the gantt to the normal mode
            gantt.collapse();
        }
    }, false);
</script>