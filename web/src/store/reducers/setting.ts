import { createSlice } from "@reduxjs/toolkit";
import defaultSetting from '@/setting.json';

export type SettingState = typeof defaultSetting;

const initialState:SettingState = defaultSetting;

const settingSlice = createSlice({
  name: 'setting',
  initialState,
  reducers: {
    setSetting(state, action) {
    }
  },
})

export const { setSetting } = settingSlice.actions
export default settingSlice.reducer
export const setting = (state) => state.setting
