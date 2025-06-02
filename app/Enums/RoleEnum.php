<?php

namespace App\Enums;

enum RoleEnum:string {
  const ADMIN = 'admin';
  const CONSUMER = 'teacher';
  const VENDOR = 'vendor';
  const DRIVER = 'student';
}

