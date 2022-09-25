<?php

function drawStar($img,$x,$y,$radius,$sides,$color,$spikness=0.5)
{
    $point =array();
    $t = 0;
    for($a = 0;$a <= 360;$a += 360/($sides*2))
    {
        $t++;
        if($t % 2 == 0)
        {
            $point[] = $x + ($radius * $spikness) * cos(deg2rad($a));
            $point[] = $y + ($radius * $spikness) * sin(deg2rad($a));
        }else{
            $point[] = $x + $radius * cos(deg2rad($a));
            $point[] = $y + $radius * sin(deg2rad($a));
        }
    }
    return imagepolygon($img,$point,$sides*2,$color);
}

?>
