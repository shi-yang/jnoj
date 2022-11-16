import { getUserInfo as getInfo } from "@/api/user";
import { removeAccessToken } from "@/utils/auth";
import { createAsyncThunk, createSlice } from "@reduxjs/toolkit";

export interface UserInfoState {
  userInfo?: {
    id?: number;
    username?: string;
    nickname?: string;
    avatar?: string;
  };
  isLogged?: boolean;
}

const initialState: UserInfoState = {
  userInfo: {},
  isLogged: false,
};

const userSlice = createSlice({
  name: 'user',
  initialState,
  reducers: {
    setUserInfo(state, action) {
      state.userInfo = action.payload
    }
  },
  extraReducers: builder => {
    builder
      .addCase(getUserInfo.fulfilled, (state: UserInfoState, action) => {
        const data = action.payload
        state.userInfo.id = data.id;
        state.userInfo.username = data.username;
        state.userInfo.avatar = data.avatar;
        state.userInfo.nickname = data.nickname;
        state.isLogged = true
      })
      .addCase(getUserInfo.rejected, (state, action) => {
        state.isLogged = false
        removeAccessToken()
      })
  }
})

export const getUserInfo = createAsyncThunk('user/info', async () => {
  const resp = await getInfo()
  return resp.data
})

export const { setUserInfo } = userSlice.actions
export default userSlice.reducer
export const userInfo = (state) => state.user.userInfo
