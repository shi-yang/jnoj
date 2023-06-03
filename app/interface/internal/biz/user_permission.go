package biz

// 定义资源常量
const (
	ResourceAll        = "*"          // 所有
	ResourceAdmin      = "admin"      // 后台
	ResourceSubmission = "submission" // 提交
	ResourceProblem    = "problem"    // 题目
	ResourceContest    = "contest"    // 比赛
	ResourceGroup      = "group"      // 小组
)

// 定义全局变量，用于保存访问权限控制实例
var accessControl *AccessControl

// 初始化访问权限控制实例
func init() {
	accessControl = NewAccessControl()
}

// 定义访问权限的结构体
type AccessControl struct {
	roleToResources map[int][]string // 角色对应的可访问资源列表
}

// 初始化访问权限
func NewAccessControl() *AccessControl {
	ac := &AccessControl{
		roleToResources: make(map[int][]string),
	}
	// 设置 超级管理员 可访问资源
	ac.roleToResources[UserRoleSuperAdmin] = []string{
		ResourceAll,
	}

	// 设置 管理员 可访问资源
	ac.roleToResources[UserRoleAdmin] = []string{
		ResourceSubmission,
		ResourceContest,
		ResourceGroup,
		ResourceProblem,
	}

	// 设置 官方用户可访问资源
	ac.roleToResources[UserRoleOfficial] = []string{
		ResourceSubmission,
		ResourceContest,
		ResourceGroup,
	}
	return ac
}

// CheckAccess 检查指定角色是否有访问指定资源的权限
func CheckAccess(role int, resource string) bool {
	resources, ok := accessControl.roleToResources[role]
	if !ok {
		// 如果角色没有对应的可访问资源，则默认任何资源都无法访问
		return false
	}

	for _, r := range resources {
		if r == resource || r == ResourceAll {
			// 如果指定资源在可访问资源列表中，则有权限访问该资源
			return true
		}
	}

	// 指定资源不在可访问资源列表中，则无权限访问该资源
	return false
}

// ListRoleResources 返回用户所拥有的角色
func ListRoleResources(role int) []string {
	resources, ok := accessControl.roleToResources[role]
	if !ok {
		// 如果角色没有对应的可访问资源，则默认任何资源都无法访问
		return nil
	}
	return resources
}
