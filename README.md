Log Analyzer
=====
An optimized log file analyzer for handling large log files with support for gzip compressed files.

TODO
-----
Install libevent library for use by polling utilities (epoll) to stream data or implement prggmr/XPSPL.

Recent Tests (2,084,665 lines, 565M uncompressed log file)
-----
4/12/13  
3.34s to jump to end of file
  
4/13/13  
1.56s to jump to end of file  
2.58s to return 84 results for an IP address  
2.40s to return 2,963 results for a given string

Credits
-----
teotwaki - Optimization tips, event-driven implementation model  
GoogleGuy - Aggregation tips  
TheHackOps - Reference to prggmr/XPSPL
