<?php

namespace App\Enums;

enum RoleEnum:string {
  const ADMIN = 'admin';
  const CONSUMER = 'consumer';
  const VENDOR = 'vendor';
  const TEACHER = 'teacher';
  const STUDENT = 'student';
  const DRIVER = 'driver';
}

