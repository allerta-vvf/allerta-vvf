<?php
var_dump(time()-@filemtime("options.txt") < 604800);