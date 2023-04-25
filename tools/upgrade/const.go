package main

import v2 "jnoj/tools/upgrade/v2"

// v1 solution.result -> v2 submission.verdict
var VerdictMap = map[int]int{
	0:  v2.SubmissionVerdictPending,           // Pending
	1:  v2.SubmissionVerdictPending,           // Pending Rejudge
	2:  v2.SubmissionVerdictPending,           // Compiling
	3:  v2.SubmissionVerdictPending,           // Running & Judging
	4:  v2.SubmissionVerdictAccepted,          // Accepted
	5:  v2.SubmissionVerdictPresentationError, // Presentation Error
	6:  v2.SubmissionVerdictWrongAnswer,       // Wrong Answer
	7:  v2.SubmissionVerdictTimeLimit,         // Time Limit Exceeded
	8:  v2.SubmissionVerdictMemoryLimit,       // Memory Limit Exceeded
	9:  v2.SubmissionVerdictWrongAnswer,       // Output Limit Exceeded
	10: v2.SubmissionVerdictRuntimeError,      // Runtime Error
	11: v2.SubmissionVerdictCompileError,      // Compile Error
	12: v2.SubmissionVerdictSysemError,        // System Error
}

var GroupStatusMap = map[int]int{
	0: v2.GroupPrivacyPrivate, //STATUS_HIDDEN
	1: v2.GroupPrivatePublic,
}

var GroupJoinPolicyMap = map[int]int{
	0: v2.GroupMembershipInvitationCode, // JOIN_POLICY_INVITE
	1: v2.GroupMembershipInvitationCode, // JOIN_POLICY_APPLICATION
	2: v2.GroupMembershipAllowAnyone,    // JOIN_POLICY_FREE
}

var ContestStatusMap = map[int]int{
	0: v2.ContestPrivacyPrivate, // 隐藏
	1: v2.ContestPrivacyPublic,  // 公开
	2: v2.ContestPrivacyPrivate, // 私有
}

var ContestType = map[int]int{
	0: v2.ContestTypeIOI,  // TYPE_EDUCATIONAL
	1: v2.ContestTypeICPC, // TYPE_RANK_SINGLE
	2: v2.ContestTypeICPC, // TYPE_RANK_GROUP
	3: v2.ContestTypeIOI,  // TYPE_HOMEWORK
	4: v2.ContestTypeOI,   // TYPE_OI
	5: v2.ContestTypeIOI,  // TYPE_IOI
}

var GroupUserRole = map[int]int{
	0: v2.GroupUserRoleMember,  // ROLE_REUSE_INVITATION
	1: v2.GroupUserRoleMember,  // ROLE_REUSE_APPLICATION
	2: v2.GroupUserRoleMember,  // ROLE_INVITING
	3: v2.GroupUserRoleMember,  // ROLE_APPLICATION
	4: v2.GroupUserRoleMember,  // ROLE_MEMBER
	5: v2.GroupUserRoleManager, // ROLE_MANAGER
	6: v2.GroupUserRoleAdmin,   // ROLE_LEADER
}
