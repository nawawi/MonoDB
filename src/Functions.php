<?php
/*
 * This file is part of the MonoDB package.
 *
 * (c) Nawawi Jamili <nawawi@rutweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monodb;

use \Symfony\Component\VarExporter\VarExporter;

class Functions {

    public static function has_with( string $haystack, $needles ) {
        foreach ( (array) $needles as $needle ) {
            if ( false !== strpos( $haystack, (string) $needle ) ) {
                return true;
            }
        }
        return false;
    }

    /**
    * end_with().
    */
    public static function end_with( string $haystack, $needles ) {
        foreach ( (array) $needles as $needle ) {
            if ( substr( $haystack, -\strlen( $needle ) ) === (string) $needle ) {
                return true;
            }
        }
        return false;
    }

    /**
    * start_with().
    */
    public static function start_with( string $haystack, $needles ) {
        foreach ( (array) $needles as $needle ) {
            if ( '' !== $needle && 0 === strpos( $haystack, $needle ) ) {
                return true;
            }
        }
        return false;
    }

    /**
    * is_file_readable().
    */
    public static function is_file_readable( string $file ) {
        if ( is_file( $file ) && is_readable( $file ) ) {
            clearstatcache( true, $file );
            return true;
        }
        return false;
    }

    /**
    * is_file_writable().
    */
    public static function is_file_writable( string $file ) {
        if ( is_file( $file ) && is_writable( $file ) ) {
            clearstatcache( true, $file );
            return true;
        }
        return false;
    }

    /**
    * int_to_char().
    */
    public static function int_to_char( $int ) {
        if ( ! \is_int( $int ) ) {
            return $int;
        }

        if ( $int < -128 || $int > 255 ) {
            return (string) $int;
        }

        if ( $int < 0 ) {
            $int += 256;
        }

        return \chr( $int );
    }

    /**
    * is_var_binary().
    */
    public static function ctype_print( $text ) {
        $text = self::int_to_char( $text );
        return ( \is_string( $text ) && '' !== $text && ! preg_match( '/[^ -~]/', $text ) );
    }

    /**
    * is_var_binary().
    */
    public static function is_var_binary( $blob ) {
        if ( \is_null( $blob ) || \is_integer( $blob ) ) {
            return false;
        }

        if ( function_exists( 'ctype_print' ) ) {
            return ! ctype_print( $blob );
        }

        return ! self::ctype_print( $blob );
    }

    /**
    * is_var_json().
    */
    public static function is_var_json( string $string ) {
        return ( \is_array( json_decode( $string, true ) )
            && ( JSON_ERROR_NONE === json_last_error() ) ? true : false
        );
    }

    /**
    * is_var_num().
    */
    public static function is_var_num( $num ) {
        return preg_match( '@^\d+$@', (string) $num );
    }

    /**
    * is_var_int().
    */
    public static function is_var_int( $num ) {
        return preg_match( '@^(\-)?\d+$@', (string) $num );
    }

    /**
    * is_var_time().
    */
    public static function is_var_time( $num ) {
        if ( self::is_var_num( $num ) && $num > 0 && $num < PHP_INT_MAX ) {
            if ( false !== date( 'Y-m-d H:i:s', (int) $num ) ) {
                return true;
            }
        }
        return false;
    }

    /**
    * encrypt().
    */
    public static function encrypt( string $string, string $epad = '!!$$@#%^&!!' ) {
        $mykey = '!!$'.$epad.'!!';
        $pad = base64_decode( $mykey );
        $encrypted = '';
        for ( $i = 0; $i < \strlen( $string ); $i++ ) {
            $encrypted .= \chr( \ord( $string[ $i ] ) ^ \ord( $pad[ $i ] ) );
        }
        return strtr( base64_encode( $encrypted ), '=/', '$@' );
    }

    /**
    * decrypt().
    */
    public static function decrypt( string $string, string $epad = '!!$$@#%^&!!' ) {
        $mykey = '!!$'.$epad.'!!';
        $pad = base64_decode( $mykey );
        $encrypted = base64_decode( strtr( $string, '$@', '=/' ) );
        $decrypted = '';
        for ( $i = 0; $i < \strlen( $encrypted ); $i++ ) {
            $decrypted .= \chr( \ord( $encrypted[ $i ] ) ^ \ord( $pad[ $i ] ) );
        }
        return $decrypted;
    }

    /**
    * is_var_stdclass().
    */
    public static function is_var_stdclass( $object ) {
        if ( $object instanceof stdClass ) {
            return true;
        }

        if ( preg_match( '@^stdClass\:\:__set_state\(.*@', var_export( $object, 1 ) ) ) {
            return true;
        }

        return false;
    }

    /**
    * is_var_closure().
    */
    public static function is_var_closure( $object ) {
        if ( $object instanceof Closure ) {
            return true;
        }

        if ( preg_match( '@^Closure\:\:__set_state\(.*@', var_export( $object, 1 ) ) ) {
            return true;
        }

        return false;
    }

    /**
    * strip_scheme().
    */
    public static function strip_scheme( string $string ) {
        return preg_replace( '@^(file://|https?://|//)@', '', \trim( $string ) );
    }

    /**
    * match_wildcard(().
    */
    public static function match_wildcard( string $string, string $matches ) {
        foreach ( (array) $matches as $match ) {
            if ( self::has_with( $match, [ '*', '?' ] ) ) {
                $wildcard_chars = [ '\*', '\?' ];
                $regexp_chars = [ '.*', '.' ];
                $regex = str_replace( $wildcard_chars, $regexp_chars, preg_quote( $match, '@' ) );

                if ( preg_match( '@^'.$regex.'$@is', $string ) ) {
                    return true;
                }
            } elseif ( $string === $match ) {
                return true;
            }
        }

        return false;
    }

    /**
    * normalize_path().
    */
    public static function normalize_path( string $path ) {
        $path = str_replace( '\\', '/', $path );
        return preg_replace( '@[/]+@', '/', $path.'/' );
    }

    /**
    * resolve_path().
    */
    public static function resolve_path( string $path ) {
        $path = self::normalize_path( $path );

        if ( self::start_with( $path, '.' ) || ! self::start_with( $path, '/' ) ) {
            $path = getcwd().'/'.$path;
        }

        $path = str_replace( '/./', '/', $path );
        do {
            $path = preg_replace( '@/[^/]+/\\.\\./@', '/', $path, 1, $cnt );
        } while ( $cnt );

        return $path;
    }

    /**
    * object_to_array().
    */
    public static function object_to_array( $object ) {
        return json_decode( json_encode( $object ), true );
    }

    /**
    * get_type().
    */
    public static function get_type( $data ) {
        $type = \gettype( $data );

        switch ( $type ) {
            case 'object':
                if ( self::is_var_stdclass( $data ) ) {
                    $type = 'stdClass';
                } elseif ( self::is_var_closure( $data ) ) {
                    $type = 'closure';
                }
                break;
            case 'string':
                if ( self::is_var_json( $data ) ) {
                    $type = 'json';
                } elseif ( self::is_var_binary( $data ) ) {
                    $type = 'binary';
                }
                break;
        }
        return $type;
    }

    /**
    * get_size().
    */
    public static function get_size( $data ) {
        return ( \is_array( $data ) || \is_object( $data ) ? sizeof( (array) $data ) : \strlen( $data ) );
    }

    /**
    * var_export().
    */
    public static function export_var( $data ) {
        return VarExporter::export( $data );
    }
}
