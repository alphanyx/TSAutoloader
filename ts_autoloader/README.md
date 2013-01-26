

This Extension is looking for the file "fileadmin/ts_autoloader.ts"
this file will be loaded and the typoscript defines the autoload pattern


Example Include:

This Setting is looking for all files with the ending .ts (*.ts) in the directory:
fileadmin/project/test/TypoScript/file_test

All files excepting the ones in the ignore schema (comma separated) will be loaded


All Typoscript files with the ending .ts, excepting the ones that start with an underscore and the file "test.ts"

plugin.tx_tsautoload {
	1 {
		type = file
		directory = fileadmin/project/test/TypoScript/file_test
		pattern = *.ts
		ignore = _*.ts, test.ts
	}
}