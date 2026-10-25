<?php

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function makeClaimCode() {
    return strtoupper(bin2hex(random_bytes(4)));
}