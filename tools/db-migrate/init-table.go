package dbmigrate

import (
	"io"
	"os"
	"strings"

	"gorm.io/gorm"
)

func MigrateInitDB(tx *gorm.DB) error {
	dbFile, err := os.Open("/go/src/jnoj/tools/db-migrate/db.sql")
	if err != nil {
		return err
	}
	defer dbFile.Close()
	sqlContent, err := io.ReadAll(dbFile)
	if err != nil {
		return err
	}
	db := tx.Begin()
	sqls := splitString(string(sqlContent), ";")
	for _, sql := range sqls {
		if sql == "" {
			continue
		}
		err := db.Exec(sql).Error
		if err != nil {
			db.Rollback()
			return err
		}
	}
	db.Commit()
	return nil
}

// 定义自定义分割函数：按照 ; 切割字符串，但不包含在 () 内
func splitString(str string, delimiter string) []string {
	var result []string
	var isInsideBrackets int
	var currentStr string

	for i := 0; i < len(str); i++ {
		ch := str[i]
		if ch == '(' {
			isInsideBrackets++
		} else if ch == ')' {
			isInsideBrackets--
		}
		if ch == ';' && isInsideBrackets == 0 {
			result = append(result, currentStr)
			currentStr = ""
		} else {
			currentStr += string(ch)
		}
	}

	// 添加最后一个分割后的字符串
	if strings.TrimSpace(currentStr) != "" {
		result = append(result, currentStr)
	}
	return result
}
