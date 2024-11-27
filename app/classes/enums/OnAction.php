<?php

namespace app\classes\enums;

enum OnAction: string {
    case SET_NULL = "SET NULL";
    case CASCADE = "CASCADE";
    case NO_ACTION = "NO ACTION";
    case RESTRICT = "RESTRICT";
}

?>
