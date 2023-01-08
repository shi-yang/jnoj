const i18n = {
  'en-US': {
    'page.title': 'Update Problem',
    'tab.baseInfo': 'Base Info',
    'tab.statement': 'Statement',
    'tab.checker': 'Checker',
    'tab.validator': 'Validator',
    'tab.solutionFiles': 'Solution files',
    'tab.tests': 'Tests',
    'tab.files': 'Files',
    'legend': 'Legend',
    'submit': 'Submit',
    'input': 'Input',
    'output': 'Output',
    'sample': 'Sample',
    'notes': 'Notes',
    'timeLimit': 'Time Limit',
    'memoryLimit': 'Memory Limit',
    'language': 'Language',
    'name': 'Name',
    'preview': 'Preview',
    'content': 'Content',
    'size': 'Size',
    'length': 'Length',
    'remark': 'Remark',
    'action': 'Action',
    'type': 'Type',
    'createdAt': 'Created At',
    'save': 'Save',

    'tests.table.isExample': 'Is Example?',
    'tests.table.inputSize': 'Input Size',
    'tests.table.outputSize': 'Output Size',
    'tests.table.inputPreview': 'Input Preview',
    'tests.table.outputPreview': 'Output Preview',

    'tests.table.name': 'Name',
    'checker.std.fcmp.cpp.title': 'Lines, doesn\'t ignore whitespaces',
    'checker.std.fcmp.cpp.intro': 'Use it to compare input and answer as sequence of lines. This checker is very strict, do not use it if you don\'t need it really.',
    'checker.std.hcmp.cpp.title': 'Single huge integer',
    'checker.std.hcmp.cpp.intro': 'Compares two signed huge (big) integers. Validates that both integers (in the output and in the answer) are well-formatted.',
    'checker.std.lcmp.cpp.title': 'Lines, ignores whitespaces',
    'checker.std.lcmp.cpp.intro': 'Compares output and answer as sequence of lines. Compares each pair of lines as tokens sequence. In other words ignores spaces mismatchings, but doesn\'t ignore new line characters mismatchings.',
    'checker.std.ncmp.cpp.title': 'Single or more int64, ignores whitespaces',
    'checker.std.ncmp.cpp.intro': 'Compare output and answer as a sequence of int64. Ignores whitespaces mismatchings.',
    'checker.std.nyesno.cpp.title': 'Zero or more yes/no, case insensetive',
    'checker.std.nyesno.cpp.intro': 'Expects zero or more "yes"/"no" (case insensetive), ignores whitespaces',
    'checker.std.rcmp4.cpp.title': 'Single or more double, max any error 1E-4',
    'checker.std.rcmp4.cpp.intro': 'Compare output and answer as a sequence of real numbers. Ignores whitespaces mismatchings. Two real numbers are considered equals if their absolute or relative error doesn\'t exceed 1E-4.',
    'checker.std.rcmp6.cpp.title': 'Single or more double, max any error 1E-6',
    'checker.std.rcmp6.cpp.intro': 'Compare output and answer as a sequence of real numbers. Ignores whitespaces mismatchings. Two real numbers are considered equals if their absolute or relative error doesn\'t exceed 1E-6.',
    'checker.std.rcmp9.cpp.title': 'Single or more double, max any error 1E-9',
    'checker.std.rcmp9.cpp.intro': 'Compare output and answer as a sequence of real numbers. Ignores whitespaces mismatchings. Two real numbers are considered equals if their absolute or relative error doesn\'t exceed 1E-9.',
    'checker.std.wcmp.cpp.title': 'Sequence of tokens',
    'checker.std.wcmp.cpp.intro': 'Compares output and answer as sequence of tokens. Ignores whitespaces mismatchings.',
    'checker.std.yesno.cpp.title': 'Single yes or no, case insensetive',
    'checker.std.yesno.cpp.intro': 'Expects "yes" or "no" (case insensetive), ignores whitespaces',
  },
  'zh-CN': {
    'page.title': '修改题目',
    'tab.baseInfo': '基本信息',
    'tab.statement': '题面描述',
    'tab.checker': '裁判程序',
    'tab.validator': '数据校验',
    'tab.tests': '测试点',
    'tab.solutionFiles': '解答文件',
    'tab.files': '文件',
    'legend': '描述',
    'submit': '提交',
    'input': '输入',
    'output': '输出',
    'sample': '样例',
    'notes': '提示',
    'timeLimit': '时间限制',
    'memoryLimit': '内存限制',
    'language': '语言',
    'name': '名称',
    'preview': '预览',
    'content': '内容',
    'size': '大小',
    'length': '长度',
    'remark': '备注',
    'action': '操作',
    'type': '类型',
    'createdAt': '创建时间',
    'save': '保存',

    'tests.table.name': '名称',
    'tests.table.isExample': '是否样例？',
    'tests.table.inputSize': '输入大小',
    'tests.table.outputSize': '输出大小',
    'tests.table.inputPreview': '输入预览',
    'tests.table.outputPreview': '输出预览',

    'checker.std.fcmp.cpp.title': '行，不忽略空格',
    'checker.std.fcmp.cpp.intro': '按行来比较用户输出和答案。这个检查器非常严格，如果你真的不需要它，请不要使用它。',
    'checker.std.hcmp.cpp.title': '单个大整数',
    'checker.std.hcmp.cpp.intro': '比较两个有符号的大整数。验证两个整数（在输出和答案中）的格式是否正确。',
    'checker.std.lcmp.cpp.title': '行比较，忽略空格【常用】',
    'checker.std.lcmp.cpp.intro': '按行来比较用户输出和答案。忽略空格不匹配，但不忽略换行符不匹配。',
    'checker.std.ncmp.cpp.title': '单个或多个 int64，忽略空格',
    'checker.std.ncmp.cpp.intro': '将输出和答案以 int64 序列进行比较。忽略空格不匹配。',
    'checker.std.nyesno.cpp.title': '0个或多个 yes/no，不区分大小写',
    'checker.std.nyesno.cpp.intro': '0个或多个 "yes"/"no" (不区分大小写)，忽略空格',
    'checker.std.rcmp4.cpp.title': '1个或多个 double，误差 1E-4',
    'checker.std.rcmp4.cpp.intro': '将输出和答案作为实数序列进行比较。忽略空格不匹配。如果两个实数的绝对或相对误差不超过 1E-4，则认为它们相等。',
    'checker.std.rcmp6.cpp.title': '1个或多个 double，误差 1E-6',
    'checker.std.rcmp6.cpp.intro': '将输出和答案作为实数序列进行比较。忽略空格不匹配。如果两个实数的绝对或相对误差不超过 1E-6，则认为它们相等。',
    'checker.std.rcmp9.cpp.title': '1个或多个 double，误差 1E-9',
    'checker.std.rcmp9.cpp.intro': '将输出和答案作为实数序列进行比较。忽略空格不匹配。如果两个实数的绝对或相对误差不超过 1E-9，则认为它们相等。',
    'checker.std.wcmp.cpp.title': '字符序列',
    'checker.std.wcmp.cpp.intro': '按行来比较用户输出的字符。忽略空格不匹配。',
    'checker.std.yesno.cpp.title': '1个或多个 yes/no，区分大小写',
    'checker.std.yesno.cpp.intro': '1个或多个 "yes" "no" (区分大小写)，忽略空格',
  },
};

export default i18n;