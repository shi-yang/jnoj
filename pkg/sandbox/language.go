package sandbox

// Language .
type Language struct {
	Code           int
	Name           string
	CompileCommand []string
	CodeFileName   string
	RunCommand     []string
	IsVMRun        bool
}

const (
	LangC      = 0
	LangCpp    = 1
	LangJava   = 2
	LangPython = 3
)

// LanguageText returns a text for the language code. It returns the empty
// string if the code is unknown.
func LanguageText(code int) string {
	switch code {
	case LangC:
		return "C"
	case LangCpp:
		return "C++"
	case LangJava:
		return "Java"
	case LangPython:
		return "Python"
	default:
		return ""
	}
}

// Languages 支持的语言
var Languages = []Language{
	{
		Code:         LangC,
		Name:         "C",
		CodeFileName: "main.c",
		CompileCommand: []string{"gcc", "main.c", "-o", "main", "-Wall", "-O2", "-lm", "--static", "-std=c99",
			"-DONLINE_JUDGE", "-save-temps", "-fmax-errors=10"},
		RunCommand: []string{"./main"},
		IsVMRun:    false,
	},
	{
		Code:         LangCpp,
		Name:         "C++",
		CodeFileName: "main.cpp",
		CompileCommand: []string{"g++", "main.cpp", "-o", "main", "-Wall", "-fno-asm", "-O2", "-lm", "--static", "-std=c++11",
			"-DONLINE_JUDGE", "-save-temps", "-fmax-errors=10"},
		RunCommand: []string{"./main"},
		IsVMRun:    false,
	},
	{
		Code:           LangJava,
		Name:           "Java",
		CodeFileName:   "Main.java",
		CompileCommand: []string{"javac", "-J-Xms64M", "-J-Xmx128M", "-encoding", "UTF-8", "Main.java"},
		RunCommand:     []string{"java", "Main"},
		IsVMRun:        true,
	},
	{
		Code:           LangPython,
		Name:           "Python",
		CodeFileName:   "main.py",
		CompileCommand: []string{"python3", "-c", "import py_compile; py_compile.compile(r'main.py')"},
		RunCommand:     []string{"python3", "main.py"},
		IsVMRun:        true,
	},
}
