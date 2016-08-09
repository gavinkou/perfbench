#!/bin/env php
<?php

$sysbench_cmd=__DIR__."/sysbench" ;

$default_args=array( "--verbosity"=>3, "--percentile"=>95 ,"--num-threads"=>1, "--max-requests"=>100000, 
                "--max-time"=>0, "--forced-shutdown"=>"off", "--thread-stack-size"=>"64k", "--tx-rate"=>0, 
                "--report-interval"=>0, "--report-checkpoints"=>NULL, "--debug"=>"off", "--validate"=>"off", 
                "--version"=>"off", "--rand-init"=>"off", "--rand-type"=>"special", "--rand-spec-iter"=>12, 
                "--rand-spec-pct"=>1, "--rand-spec-res"=>75, "--rand-seed"=>0,"--rand-pareto-h"=>0.2) ;
$default_test="cpu";
$default_command="run";

$test= ( $argc==1? $default_test : $argv[1] ) ;
$args= ( $argc >= 2 ? array_slice( $argv, 2 ) : array() ) ;
$command=  $default_command;

$test_default_args=array(
	//'cpu'=> array("--cpu-max-prime"=>10000), 
	'cpu'=> array("--cpu-max-prime"=>100), //test only

	'fileio'=>array("--file-test-mode"=>"seqrd",
            "--file-num"=>64, 
            "--file-block-size" => 16384 , 
            "--file-total-size" => "1G",
            "--file-io-mode" => "sync", 
            "--file-extra-flags"=>"" ,
            "--file-fsync-freq" => 100,  
            "--file-fsync-all"=>"off",
            "--file-fsync-end" => "on", 
            "--file-fsync-mode"=>"fsync", 
            "--file-merged-requests" => 0,  
            "--file-rw-ratio"=>1.5) ,
	'memory'=> array("--memory-block-size" => "1k", "--memory-total-size" => "100G", "--memory-scope"=>"global", "--memory-hugetlb"=>"off", "--memory-oper"=>"write", "--memory-access-mode"=>"seq"), 
        'threads' => array('--thread-yields' => 1000, '--thread-locks'=>8 ),
        'mutex' => array('--mutex-num' => 4096, '--mutex-locks'=> 50000, '--mutex-loops' => 10000 ),
	);
if( $argc ==1 || $argc == 2 && in_array( trim($argv[1]), array('-h','--help') ) ) {
    help();
    exit;
}
if( @trim($argv[2]) == 'prepare' ) {
prepare();
exit;
}
if( @trim( $argv[2] ) == 'cleanup' ) {
cleanup();
exit;
}


$run= $sysbench_cmd." --test=".$test. " " . parse_opt() . " " .$command;
//echo $run;
//echo $run;
exec($run, $output );
//cleanup();
$result=parse_output($output);
foreach( $result as $key => $value ) {
    echo $key. "=". $value,"\n";
}
echo "\n";

function parse_opt(){
    global $default_args, $test_default_args, $test, $args;
    $all_args = array_merge($default_args, $test_default_args[$test]) ;
    if( in_array('-h',$args) )  {
    help();
    exit;
    }
    foreach( $args as $key=>$arg ) {
        @list($arg_name,$arg_value)=explode('=',trim($arg)) ;
        if(array_key_exists($arg_name,$all_args) ) {
            $all_args[ $arg_name ] = $arg_value ;
        }
    }
    $ret = "" ;
    foreach( $all_args as $arg_name => $arg_value ) {
        $ret .= $arg_name. "=". $arg_value ." " ;
    }
    return $ret ;
    
}
function parse_output($output){
    global $test;  
    $result=array_filter( $output, function($l){return trim($l) ;} ) ; //delete white space line
    $r=array();
    foreach( $result as $line ) {
    //    if( trim( $test ) == "fileio" ) {
            if( preg_match('/^Operations performed:  (?<read_ops>\d+) reads, (?<write_ops>\d+) writes, (?<other_ops>\d+) Other = (?<total_ops>\d+) Total/', $line, $matches) ) {
                $r['read_ops']  =$matches['read_ops'];
                $r['write_ops']  =$matches['write_ops'];
                $r['other_ops']  =$matches['other_ops'];
                $r['total_ops']  =$matches['total_ops'];
            }
            unset( $matches ) ;
            if( preg_match('/^Read (?<read_bits>.+)  Written (?<written_bits>.+)  Total transferred (?<total_bits>.+)  \((?<speed>.+)\)/', $line, $matches) ) {
                $r['read_bits'] = $matches['read_bits'];
                $r['written_bits'] = $matches['written_bits'];
                $r['total_bits'] = $matches['total_bits'];
                $r['speed'] = $matches['speed'];
            }
            unset( $matches ) ;
            if( preg_match('/^(?<ops_per_sec>.+) Requests\/sec executed/', $line, $matches ) ) {
                $r['ops_per_sec'] = $matches['ops_per_sec'];
            }
     //   }

        unset( $matches ) ;
        if( preg_match('/total time:\s+(?<total_time>.+)/', $line, $matches ) ) {
            $r['total_time'] = $matches['total_time']; 
        }
        unset( $matches ) ;
        if( preg_match('/total number of events:\s+(?<total_ops>\d+)/', $line, $matches ) ) {
            $r['total_ops'] = $matches['total_ops']; 
        }
        unset( $matches ) ;
        if( preg_match('/total time taken by event execution:\s+(?<total_ops_time>.+)/', $line, $matches ) ) {
            $r['total_ops_time'] = $matches['total_ops_time']; 
        }
        unset( $matches ) ;
        if( preg_match('/^\s+min:\s+(?<op_min_time>.+)/', $line, $matches ) ) {
            $r['op_min_time'] = $matches['op_min_time']; 
        }
        unset( $matches ) ;
        if( preg_match('/^\s+avg:\s+(?<op_avg_time>.+)/', $line, $matches ) ) {
            $r['op_avg_time'] = $matches['op_avg_time']; 
        }
        unset( $matches ) ;
        if( preg_match('/^\s+max:\s+(?<op_max_time>.+)/', $line, $matches ) ) {
            $r['op_max_time'] = $matches['op_max_time']; 
        }
        unset( $matches ) ;
        if( preg_match('/^\s+approx.  (?<percentile>\d+) percentile:\s+(?<op_percentile_time>.+)/', $line, $matches ) ) {
            $r[ $matches['percentile'].'_percentile'] = $matches['op_percentile_time']; 
        }

        }
        return $r ;
}

function prepare(){
    global $test, $sysbench_cmd, $default_args, $test_default_args , $test_arg;
    if( trim($test) == "fileio" ) { 
        if( ! file_exists( __DIR__."/test_file.0") ) {
	    $run= $sysbench_cmd." --test=fileio ".parse_opt()." prepare";
            echo "preparing with:\n",$run,"\n" ; 
	    exec($run,$tmp);
            echo "prepare done\n";
	}
    }
}

function cleanup(){
    global $test, $default_args, $sysbench_cmd ;
    if( trim($test) == "fileio" ) {
	    $run= $sysbench_cmd. " --test=".$test. " " . parse_opt() . " cleanup";
	    exec($run,$tmp);
    }
}

function help(){
    global $sysbench_cmd, $default_args,$test_default_args;
    echo $sysbench_cmd, "test_name [test_args]\n";
    echo "the following is the default global args:\n";
    foreach( $default_args as $key=>$value ) {
    echo $key,":",$value,"\n";
    }
   
    echo "the following is the default args for each test model:\n";
    foreach( $test_default_args as $test_mod => $mod_args ) {
        echo "\t$test_mod:\n";
        foreach( $mod_args as $arg_name=>$arg_value){
            echo "\t\t$arg_name : $arg_value\n";
        }
    }
    echo 'more help info please refer: '. $sysbench_cmd ." -h\n";
}
