# benchmarking
The tool at present allows you to plot an extensible number of graphs of provided csv files.
The output takes the form of a frametime/time graph, a percentile graph, a histogram and a table
or relevant information, such as the min, max, average and standard deviation of frametimes.

The file can be used in the command line by being called in the following manner:

php /path/build_graphs.php -f csv_path_1,csv_path2,csv_path3,etc -t -p -h -d

the -f parameter takes a list of any number of csv files to compare, with a minimum of 1.
-t,-p,-h and -d if provided mute different parts of the output.
-t :: makes the frametime/time graph not be created 
-p :: makes the percentile graph not be created 
-h :: makes the histogram not be created 
-d :: makes the data table not be created 