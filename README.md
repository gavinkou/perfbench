# perfbench
performance benchmark utility, try to make it easy and simply to test performance benchmark and to parse the result

this project provide a user friendly performance benchmark tool set, includeing

1. pre-compiled V0.5 sysbench, refer https://github.com/akopytov/sysbench 
2. a php wrapper to provide
  1. well parsed test result output
  2. easy to prepare/cleanup/run

```
[goug@zabbix001:~/GitRoot/perfbench]$ ./sysbench.php fileio prepare
preparing with:
/u0/goug/GitRoot/perfbench/sysbench --test=fileio --verbosity=3 --percentile=95 --num-threads=1 --max-requests=100000 --max-time=0 --forced-shutdown=off --thread-stack-size=64k --tx-rate=0 --report-interval=0 --report-checkpoints= --debug=off --validate=off --version=off --rand-init=off --rand-type=special --rand-spec-iter=12 --rand-spec-pct=1 --rand-spec-res=75 --rand-seed=0 --rand-pareto-h=0.2 --file-test-mode=seqrd --file-num=64 --file-block-size=16384 --file-total-size=16G --file-io-mode=sync --file-extra-flags= --file-fsync-freq=100 --file-fsync-all=off --file-fsync-end=on --file-fsync-mode=fsync --file-merged-requests=0 --file-rw-ratio=1.5  prepare

prepare done
[goug@zabbix001:~/GitRoot/perfbench]$
[goug@zabbix001:~/GitRoot/perfbench]$ ./sysbench.php fileio
read_ops=100000
write_ops=0
other_ops=0
total_ops=100000
read_bits=1.5259Gb
written_bits=0b
total_bits=1.5259Gb
speed=143.27Mb/sec
ops_per_sec= 9169.33
total_time=10.9059s
total_ops_time=10.8150s
op_min_time=0.01ms
op_avg_time=0.11ms
op_max_time=21.22ms
95_percentile=0.50ms

[goug@zabbix001:~/GitRoot/perfbench]$
[goug@zabbix001:~/GitRoot/perfbench]$ ./sysbench.php cpu
total_time=0.7810s
total_ops=100000
total_ops_time=0.7420s
op_min_time=0.01ms
op_avg_time=0.01ms
op_max_time=0.16ms
95_percentile=0.01ms
```
