import React, { useMemo } from 'react';
import { UserInfoState, userInfo } from '@/store/reducers/user';
import { useAppSelector } from '@/hooks';
import authentication, { AuthParams } from '@/utils/authentication';

type PermissionWrapperProps = AuthParams & {
  backup?: React.ReactNode;
};

const PermissionWrapper = (
  props: React.PropsWithChildren<PermissionWrapperProps>
) => {
  const { backup, requiredPermissions, oneOfPerm } = props;
  const user = useAppSelector(userInfo);

  const hasPermission = useMemo(() => {
    return authentication(
        {requiredPermissions, oneOfPerm},
        user.permissions
    );
  },[oneOfPerm, requiredPermissions, user.permissions]);
  if (hasPermission) {
    return <>{convertReactElement(props.children)}</>;
  }
  if (backup) {
    return <>{convertReactElement(backup)}</>;
  }
  return null;
};

function convertReactElement(node: React.ReactNode): React.ReactElement {
  if (!React.isValidElement(node)) {
    return <>{node}</>;
  }
  return node;
}

export default PermissionWrapper;
