import { createSlice } from "@reduxjs/toolkit";

export interface SettingState {
  webSite: string
}

const initialState: SettingState = {
  webSite: 'aa'
};

const settingSlice = createSlice({
  name: 'setting',
  initialState,
  reducers: {
    setWebSite(state, action) {
    }
  },
})

export const { setWebSite } = settingSlice.actions
export default settingSlice.reducer
export const webSite = (state) => state.webSite
