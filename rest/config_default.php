<?php

class Env
{
    public static function DB_HOST()
    {
        return Env::get_env("DB_HOST", "localhost");
    }
    public static function DB_USERNAME()
    {
        return Env::get_env("DB_USERNAME", "user_20002309");
    }
    public static function DB_PASSWORD()
    {
        return Env::get_env("DB_PASSWORD", "SR543K");
    }
    public static function DB_SCHEME()
    {
        return Env::get_env("DB_SCHEME", "db_200023090");
    }
    public static function DB_PORT()
    {
        return Env::get_env("DB_PORT", "3306");
    }
    public static function GMAILPASS()
    {
        return Env::get_env("GMAILPASS", "574ec90d3822550a2c6ad8ca9b49949a-5d9bd83c-05850366");
    }
    public static function NEXMOID()
    {
        return Env::get_env("NEXMOID", "f6fb31cd");
    }
    public static function NEXMOAPIKEY()
    {
        return Env::get_env("NEXMOAPIKEY", "jOUqvTryPgbrF9eZ");
    }
    

  //ovo nismo dirali!!
    public static function jwtSecret()
    {
        return Env::get_env("JWT_SECRET", "ezcb9s8UcF");
    }
  //isto !!
    public static function get_env($name, $default)
    {
        return isset($_ENV[$name]) && trim($_ENV[$name]) != '' ? $_ENV[$name] : $default;
    }
}