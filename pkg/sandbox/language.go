package sandbox

// Language .
type Language struct {
	Name           string
	CompileCommand []string
	CodeFileName   string
	RunCommand     []string
	IsVMRun        bool
}

const (
	LANG_C       = 0
	Lang_CPP     = 1
	LANG_JAVA    = 2
	LANG_PYTHON3 = 3
)

// Languages .
var Languages = []Language{
	{
		Name:         "C",
		CodeFileName: "main.c",
		CompileCommand: []string{"gcc", "main.c", "-o", "main", "-Wall", "-O2", "-lm", "--static", "-std=c99",
			"-DONLINE_JUDGE", "-save-temps", "-fmax-errors=10"},
		RunCommand: []string{"./main"},
		IsVMRun:    false,
	},
	{
		Name:         "C++",
		CodeFileName: "main.cpp",
		CompileCommand: []string{"g++", "main.cpp", "-o", "main", "-Wall", "-fno-asm", "-O2", "-lm", "--static", "-std=c++11",
			"-DONLINE_JUDGE", "-save-temps", "-fmax-errors=10"},
		RunCommand: []string{"./main"},
		IsVMRun:    false,
	},
	{
		Name:           "Java",
		CodeFileName:   "Main.java",
		CompileCommand: []string{"javac", "-J-Xms64M", "-J-Xmx128M", "-encoding", "UTF-8", "Main.java"},
		RunCommand:     []string{"java", "Main"},
		IsVMRun:        true,
	},
	{
		Name:           "Python",
		CodeFileName:   "main.py",
		CompileCommand: []string{"python3", "-c", "import py_compile; py_compile.compile(r'main.py')"},
		RunCommand:     []string{"python3", "main.py"},
		IsVMRun:        true,
	},
}
