#!/bin/bash
# Basic info from a Ubuntu ECC system, including:
#
#   Number of CPUs, TOTAL RAM (KB), Disk Free (/ partition), CPU Load Average (last 1, 5, and 15-minute period)
#
CPUTOTAL=`grep "^processor" /proc/cpuinfo | wc -l`
MEMTOTAL=`grep "^MemTotal" /proc/meminfo | sed -e "s;^MemTotal:.* \([1-9].*\) .*$;\1;g"`
DISKFREE=`df -h / | awk '{ print $4 }' | tail -1`
LOADAVG=`uptime | awk '{ print $10 $11 $12 }'`
echo -n "${CPUTOTAL},${MEMTOTAL},${DISKFREE},${LOADAVG}"
