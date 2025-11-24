<?= $output == null ? "hi enter mesh data in box beloww" : ($output ? "MESH VALID!" : "MESH NOT VALID!") ?>
<?=var_dump($output)?>
<form action="/asset/meshvalidate" method="post">
    <textarea name="mesh" cols="64" rows="20"></textarea>
    <input type="submit">
</form>