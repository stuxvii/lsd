<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/normalize.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <title>Please stand-by.</title>
    <meta name="robots" content="noindex">
    <style>
        <?php $primary_hex ='#ffffff';
        $secondary_hex ='#000000';

        ?>:root {
            --primary-color: <?=$secondary_hex ?>;
            --secondary-color: <?=$secondary_hex ?>;
        }

        * {
            accent-color: var(--secondary-color);
        }

        body {
            background-image: linear-gradient(<?=$secondary_hex ?>cc,
                    <?=$secondary_hex ?>ff),
                var(--bgimg);
        }

        <?php $font_family =$GLOBALS['fonts_list'][0]['font_family'];
        $font_url =$GLOBALS['fonts_list'][0]['url'];

        ?>@font-face {
            font-family: <?=$font_family ?>;
            src: url('<?= $font_url ?>');
        }

        body {
            font-family: '<?= $font_family ?>';
        }

        .border {
            --secondary-color: #ffffff;
            color: var(--secondary-color);
        }
    </style>
</head>

<body>
    <div class="sidebars" style='flex-direction: column;'>
        <div class="main">
            <div class='content'>
                <div class="maintenance_image_div focus">
                    <img src="/assets/images/maintenanceowo.png">
                </div>
                <div class='border fc aifs' style="">
                    <span>LSDBLOX is currently</span>
                    <span>under maintenance.</span>
                    <span>check #announcements,</span>
                    <span>#acids-yapzone and</span>
                    <span>#lsdblox for information.</span>
                    <br>
                    <button id="play">play skinz - 8485</button>
                    <audio id="song" src="/assets/audio/skinz.opus"></audio>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("play").addEventListener("click", function(){
            document.getElementById("song").play();
        })
    </script>
</body>

</html>