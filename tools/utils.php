<?php

function getVal($value, $default = NULL) {
    if (isset($value)) {
        if (is_array($value)) {
            return $value;
        }
        return htmlspecialchars($value);
    }
    return $default;
}
function getVar($tab, $index, $default = NULL) {
    if (isset($tab[$index])) {
        return getVal($tab[$index]);
    }
    return $default;
}

