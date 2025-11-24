<?php
$start = microtime(true);

if (isset($_GET['raw'])) {
    $cooldown_seconds = 1;
    $cooldown_file_path = '/var/tmp/api_cooldowns/';
    $ratelimitmsg = "You're being ratelimited. Try again after 1000 miliseconds.";
    $cooldown_file = $cooldown_file_path . hash('sha256',$_SERVER["REMOTE_ADDR"]) . '.time';

    if (!is_dir($cooldown_file_path)) {
        if (!mkdir($cooldown_file_path, 0777, true)) {
            error_log('Failed to create cooldown directory: ' . $cooldown_file_path);
        }
    }

    $current_time = microtime(true);
    $last_render_time = 0.0;

    if (file_exists($cooldown_file)) {
        $last_render_time = (float) file_get_contents($cooldown_file);
    }

    $time_since_last_call = $current_time - $last_render_time;

    if ($time_since_last_call < $cooldown_seconds) {
        $wait_time = $cooldown_seconds - $time_since_last_call;
        header("Retry-After: " . round($wait_time, 3));
        header("Content-type: application/json");
        $returndata = [
            'ServerLoad' => $ratelimitmsg,
            'RequestExecutionTime' => $ratelimitmsg,
            'TotalGlobalEconomy' => $ratelimitmsg,
            'Uptime' => [
                'Seconds' => $ratelimitmsg,
                'Minutes' => $ratelimitmsg,
                'Hours' => $ratelimitmsg,
                'Days' => $ratelimitmsg,
            ],
        ];
        http_response_code(429);
        echo json_encode($returndata);
        exit;
    }

    function percentloadavg()
    {  // https://www.php.net/manual/en/function.sys-getloadavg.php#126283
        $cpu_count = 1;
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cpu_count = count($matches[0]);
        }

        $sys_getloadavg = sys_getloadavg();
        $sys_getloadavg[0] = $sys_getloadavg[0] / $cpu_count;
        $sys_getloadavg[1] = $sys_getloadavg[1] / $cpu_count;
        $sys_getloadavg[2] = $sys_getloadavg[2] / $cpu_count;

        return $sys_getloadavg;
    }

    // GET TOTAL ECONOMY (amount of total money for everyone)
    $stmt = $this->db->prepare('SELECT SUM(money) as total FROM economy');
    $stmt->execute();
    $totalmunz = (int)($stmt->fetch()['total'] ?? 0);
    // get meows
    $stmt = $this->db->prepare('SELECT amount FROM analytics where type = 1');
    $stmt->execute();
    $meows = (int)($stmt->fetch()['amount'] ?? 0);

    $str = @file_get_contents('/proc/uptime');
    $num = floatval($str);
    $secs = fmod($num, 60);
    $num = intdiv($num, 60);
    $mins = $num % 60;
    $num = intdiv($num, 60);
    $hours = $num % 24;
    $num = intdiv($num, 24);
    $days = $num;
    $exectime = microtime(true) - $start;

    $returndata = [
        'ServerLoad' => percentloadavg()[0] * 100,
        'RequestExecutionTime' => $exectime,
        'TotalGlobalEconomy' => $totalmunz,
        'Meows' => $meows,
        'Uptime' => [
            'Seconds' => $secs,
            'Minutes' => $mins,
            'Hours' => $hours,
            'Days' => $days,
        ],
    ];
    header("Content-type: application/json");
    echo json_encode($returndata);
    if (file_put_contents($cooldown_file, $current_time) === false) {
        error_log('Failed to write cooldown time to: ' . $cooldown_file);
    }
    exit;
}
?>
<div class=" fc">
    <span>Updating in <span id="countdown">x</span>s</span>
<div class="border fc aifs">
    <span>Current server load: <span id="load" title="May not be 100% accurate.">xx</span>%</span>
    <span>Total global economy: ¥<span id="munz"></span></span>
    <span>meowers: <span id="meow"></span></span>
    <span title="(DD:HH:MM:SS)">Server uptime: <span id="uptime">--:--:--:--</span></span>
    <span>Request took: <span id="responsetime">--</span>s
    </span>
</div>
<div class="border fc aifs">
    <span><span style="color:var(--primary-color);background-color:var(--secondary-color);padding:3px;margin-right:4px;">GET</span>http://lsdblox.cc/info/status?raw</span>
    <span>Cooldown: 1000ms</span>
</div>
</div>
<script>

const spanload = document.getElementById("load");
const spanmunz = document.getElementById("munz");
const spanmeow = document.getElementById("meow");
const spanuptime = document.getElementById("uptime");
const spanresponsetime = document.getElementById("responsetime");
const spanupdatecountdown = document.getElementById("countdown");

async function getserverdata() {
    try {
        const request = await fetch("/info/status?raw", {
            method: 'GET'
        });

        const resp = await request.json();
        const stat = request.status;

        if (request.ok) {
            return resp;
        } else {
            return "Unhandled error: ", resp, " With error code: ", stat;
        }
    } catch (error) {
        console.error("Gasp! An error. ", error)
    }
}

function padzero(number) { // CODE REUTILIZATION!!!!!!!!
    if (number < 10) {
        return "0" + String(number)
    } else {
        return String(number)
    }
};

async function setserverdata() {
    try {
        const serverdata = await getserverdata();
        if (typeof serverdata === 'object' && serverdata !== null) {
            spanload.textContent = Math.round(serverdata.ServerLoad);
            spanmunz.textContent = serverdata.TotalGlobalEconomy;
            uptime = padzero(serverdata.Uptime.Days) + ":" + padzero(serverdata.Uptime.Hours) + ":" + padzero(serverdata.Uptime.Minutes) + ":" + padzero(Math.round(serverdata.Uptime.Seconds));
            spanuptime.textContent = uptime;
            spanmeow.textContent = serverdata.Meows;
            const exectimefloat = serverdata.RequestExecutionTime;
            const exectime = exectimefloat.toFixed(4);
            spanresponsetime.textContent = exectime;
        } else {
            console.error("error:", serverdata);
        }
        
    } catch (error) {
        console.error("???? what:", error);
    }
}
setserverdata();

// probably the most efficient piece of code i've ever written in my life
let secs = 50;
function countdown() {
    if (secs < 1) {
        secs = 50;
        setserverdata();
    }
    spanupdatecountdown.textContent = secs/10;
    secs = secs - 1
}

setInterval(countdown, 100); // so it repeats 
</script>