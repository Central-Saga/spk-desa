<?php

namespace App\Enums;

enum AksiAudit: string
{
    case Login = 'login';
    case Logout = 'logout';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Print = 'print';
    case ComputeNilai = 'compute_nilai';
}
