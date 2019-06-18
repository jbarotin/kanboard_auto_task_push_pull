plugin=auto_task_push_pull

all:
	@ echo "Build archive for plugin ${plugin} version=${version}"
	@ git archive HEAD --prefix=${plugin}/ --format=zip -o ${plugin}-${version}.zip
