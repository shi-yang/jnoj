package auth

import (
	"context"
	"fmt"
	"reflect"
	"strconv"
	"strings"
	"time"

	"github.com/go-kratos/kratos/v2/middleware"
	"github.com/go-kratos/kratos/v2/middleware/auth/jwt"
	"github.com/go-kratos/kratos/v2/transport"
	jwt2 "github.com/golang-jwt/jwt/v4"
)

// TODO Key最好需要放到配置文件中去，写到代码中极不安全
const Key = "xTtbTjnc5KmBfRYf3b1pMjf1KxFjaQE1"

const (
	// bearerWord the bearer key word for authorization
	bearerWord string = "Bearer"

	// bearerFormat authorization token format
	bearerFormat string = "Bearer %s"

	// authorizationKey holds the key used to store the JWT Token in the request tokenHeader.
	authorizationKey string = "Authorization"

	// reason holds the error reason.
	reason string = "UNAUTHORIZED"
)

// User 必须要携带 jwt token 才能访问接口
func User() middleware.Middleware {
	return jwt.Server(func(token *jwt2.Token) (interface{}, error) {
		return []byte(Key), nil
	}, jwt.WithSigningMethod(jwt2.SigningMethodHS256), jwt.WithClaims(func() jwt2.Claims {
		return &jwt2.MapClaims{}
	}))
}

// Guest 可携带可不携带。用于某些接口根据用户的登录情况不同返回对应的数据
func Guest() middleware.Middleware {
	keyFunc := func(token *jwt2.Token) (interface{}, error) {
		return []byte(Key), nil
	}
	return func(handler middleware.Handler) middleware.Handler {
		return func(ctx context.Context, req interface{}) (interface{}, error) {
			if header, ok := transport.FromServerContext(ctx); ok {
				auths := strings.SplitN(header.RequestHeader().Get(authorizationKey), " ", 2)
				if len(auths) != 2 || !strings.EqualFold(auths[0], bearerWord) {
					return handler(ctx, req)
				}
				jwtToken := auths[1]
				var (
					tokenInfo *jwt2.Token
					err       error
				)
				tokenInfo, err = jwt2.ParseWithClaims(jwtToken, &jwt2.MapClaims{}, keyFunc)
				if err != nil {
					return handler(ctx, req)
				}
				if tokenInfo.Valid {
					ctx = jwt.NewContext(ctx, tokenInfo.Claims)
				}
			}
			return handler(ctx, req)
		}
	}
}

func GenerateToken(userID int, userRole int) (string, error) {
	nowTime := time.Now()
	expireTime := nowTime.Add(7 * 24 * time.Hour)
	date := jwt2.NewNumericDate(expireTime)
	tokenClaims := jwt2.NewWithClaims(jwt2.SigningMethodHS256, jwt2.MapClaims{
		"userId":   userID,
		"userRole": userRole,
		"exp":      date,
	})
	return tokenClaims.SignedString([]byte(Key))
}

func GetUserID(ctx context.Context) (userId int, userRole int) {
	token, ok := jwt.FromContext(ctx)
	if !ok {
		return 0, -1
	}
	if err := token.Valid(); err != nil {
		return 0, -1
	}
	claims, ok := token.(*jwt2.MapClaims)
	if !ok || claims.Valid() != nil {
		return 0, -1
	}
	userId, _ = interfaceToInt((*claims)["userId"])
	userRole, _ = interfaceToInt((*claims)["userRole"])
	return
}

func interfaceToInt(value interface{}) (int, error) {
	rv := reflect.ValueOf(value)
	switch rv.Kind() {
	case reflect.Int, reflect.Int8, reflect.Int16, reflect.Int32, reflect.Int64:
		return int(rv.Int()), nil
	case reflect.Uint, reflect.Uint8, reflect.Uint16, reflect.Uint32, reflect.Uint64, reflect.Uintptr:
		return int(rv.Uint()), nil
	case reflect.Float32, reflect.Float64:
		return int(rv.Float()), nil
	case reflect.String:
		i, err := strconv.Atoi(rv.String())
		if err != nil {
			return 0, err
		}
		return i, nil
	default:
		return 0, fmt.Errorf("cannot convert %v to int", value)
	}
}
