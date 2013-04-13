Log Analyzer
=====
An optimized log file analyzer for handling large log files with support for gzip compressed files.

TODO
-----
Install libevent library for use by polling utilities (epoll) to stream data or implement prggmr/XPSPL.

Recent Tests
-----
4/12/13 - 3.34s to get to line 2,084,665 of an uncompressed 565M Apache access log file  
4/13/13 - 1.6s to get to line 2,084,665 (pointer at position 592,350,082) of an uncompressed 565M Apache access.log text file

Credits
-----
teotwaki - Optimization tips  
GoogleGuy - Aggregation tips  
TheHackOps - Reference to prggmr/XPSPL
