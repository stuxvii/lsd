<div class="aifs">
    <form method="post" action="/admin/cache/asset" class="fc aifs" id="clear_cache"
        onsubmit="return confirm('Do you wish to proceed with your action?');">
        <input type="submit" value="Clear asset render cache">
        <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
    </form>

    <form method="post" action="/admin/cache/avatar" class="fc aifs" id="clear_cache"
        onsubmit="return confirm('Do you wish to proceed with your action?');">
        <input type="submit" value="Clear avatar render cache">
        <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
    </form>

    <form method="post" action="/admin/session/refresh" class="fr aifs" id="clear_cache"
        onsubmit="return confirm('Do you wish to proceed with your action?');">
        <input type="number" name="id" placeholder="0">
        <input type="submit" value="Refresh session">
        <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
    </form>

    <form method="post" action="/admin/session/refresh_all" class="aifs" id="clear_cache"
        onsubmit="return confirm('Do you wish to proceed with your action?');">
        <input type="submit" value="Refresh every session for every user">
        <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
    </form>
</div>
