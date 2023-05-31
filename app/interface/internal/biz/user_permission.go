package biz

// 定义资源常量
const (
	ResourceAdmin      = iota // 后台
	ResourceSubmission        // 提交
	ResourceProblem           // 题目
	ResourceContest           // 比赛
	ResourceGroup             // 小组
)

// 定义全局变量，用于保存访问权限控制实例
var accessControl *AccessControl

// 初始化访问权限控制实例
func init() {
	accessControl = NewAccessControl()
}

// 定义访问权限的结构体
type AccessControl struct {
	resourceToRoles map[int][]int // 资源对应的可访问角色列表
}

// 初始化访问权限
func NewAccessControl() *AccessControl {
	ac := &AccessControl{
		resourceToRoles: make(map[int][]int),
	}
	// 设置 Admin 所有资源可供 超级管理员访问
	ac.resourceToRoles[ResourceAdmin] = []int{UserRoleSuperAdmin}

	// 设置 Submission 所有资源可供 官方用户、管理员、超级管理员访问
	ac.resourceToRoles[ResourceSubmission] = []int{UserRoleOfficial, UserRoleAdmin, UserRoleSuperAdmin}

	// 设置 Contest 所有资源可供 管理员、超级管理员访问
	ac.resourceToRoles[ResourceContest] = []int{UserRoleAdmin, UserRoleSuperAdmin}

	// 设置 Group 所有资源可供 管理员、超级管理员访问
	ac.resourceToRoles[ResourceGroup] = []int{UserRoleAdmin, UserRoleSuperAdmin}

	// 设置 Problem 所有资源可供 管理员、超级管理员访问
	ac.resourceToRoles[ResourceProblem] = []int{UserRoleAdmin, UserRoleSuperAdmin}
	return ac
}

// 检查指定角色是否有访问指定资源的权限
func CheckAccess(role int, resource int) bool {
	roles, ok := accessControl.resourceToRoles[resource]
	if !ok {
		// 如果资源没有对应的可访问角色，则默认任何角色都无法访问
		return false
	}

	for _, r := range roles {
		if r == role {
			// 如果指定角色在可访问角色列表中，则有权限访问该资源
			return true
		}
	}

	// 指定角色不在可访问角色列表中，则无权限访问该资源
	return false
}
