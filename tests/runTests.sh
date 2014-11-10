cd ..
for file in ./tests/*Test
do
	echo ~~~~~~~~~~~~~~~~~
	echo TESTING ${file}
	./phpunit.phar ${file}
done
cd tests
