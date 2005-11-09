<div id="toolbox">
  <h3>{$pm_text['pmtoolbox']} :: {$pm_text['pendingreq']}</h3>

  <fieldset class="admin">
    <legend>{$pm_text['pendingreq']}</legend>

    <?php if (!count($pendings)): ?>
    {$pm_text['nopendingreq']}
    <?php else: ?>
    <table class="history" border="1">
      <tr>
        <th>{$admin_text['eventdesc']}</th>
        <th>{$admin_text['requestedby']}</th>
        <th>{$admin_text['daterequested']}</th>
        <th>{$pm_text['reasongiven']}</th>
      </tr>
      <?php foreach ($pendings as $req): ?>
      <tr>
        <?php if ($req['request_type'] == 1) : ?>
        {$admin_text['closetask']} -
        <a href="{$fs->CreateURL('details', $req['task_id'])}">FS#{$req['task_id']} :
          {$req['item_summary']}</a>
        <?php elseif ($req['request_type'] == 2) : ?>
        {$admin_text['reopentask']} -
        <a href="{$fs->CreateURL('details', $req['task_id'])}">FS#{$req['task_id']} :
          {$req['item_summary']}</a>
        <?php elseif ($req['request_type'] == 3) : ?>
        {$admin_text['applymember']}
        <?php endif; ?>
        <td>$request_type</td>";
        <td>{$fs->LinkedUserName($req['user_id'])}</td>
        <td>{$fs->formatDate($req['time_submitted'], true)}</td>
        <td>{$req['reason_given']}</td>
        <td>
          <a href="#" class="button" onclick="showhidestuff('denyform{$req['request_id']}');">{$pm_text['deny']}</a>
          <div id="denyform{$req['request_id']}" class="denyform">
            <form action="{$baseurl}" method="post">
              <div>
                <input type="hidden" name="do" value="modify" />
                <input type="hidden" name="action" value="denypmreq" />
                <input type="hidden" name="prev_page" value="{$_SERVER['REQUEST_URI']}" />
                <input type="hidden" name="req_id" value="{$req['request_id']}" />
                {$pm_text['givereason']}
                <textarea name="deny_reason"></textarea>
                <br />
                <input class="adminbutton" type="submit" value="{$pm_text['deny']}" />
              </div>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>

  </fieldset>
</div>