import React, { useEffect, useState } from 'react';
import { Modal, Form, Input, Message, Radio, Button, Upload, Progress} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { createUserBadge, getUserBadge, updateUserBadge } from '@/api/admin/user';
import { IconEdit, IconPlus } from '@arco-design/web-react/icon';
import { UploadItem } from '@arco-design/web-react/es/Upload';
import { UserBadgeType } from './constants';
const FormItem = Form.Item;

function CreateModal({callback}: {callback: () => void}) {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [file, setFile] = React.useState<UploadItem>();
  const [fileGif, setFileGif] = React.useState<UploadItem>();
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const cs = `arco-upload-list-item${file && file.status === 'error' ? ' is-error' : ''}`;

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      const formData = new FormData();
      formData.append('name', values.name);
      formData.append('type', values.type);
      formData.append('image', values.image[0].originFile);
      formData.append('imageGif', values.imageGif[0].originFile);
      createUserBadge(formData).then(res => {
        Message.info('创建成功');
        setVisible(false);
      })
      .finally(() => {
        setConfirmLoading(false);
        callback();
      });
    });
  }

  return (
    <div>
      <Button type='primary' icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['searchTable.operations.add']}
      </Button>
      <Modal
        title='创建用户勋章'
        style={{width: 800}}
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form
          form={form}
        >
          <FormItem label='名称' required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </FormItem>
          <FormItem label='类型' required field='type' rules={[{ required: true }]}>
            <Radio.Group
              type='button'
            >
              {Object.keys(UserBadgeType).map(key => (
                <Radio key={key} value={UserBadgeType[key]}>
                  {t[`user.form.user.badge.type.${UserBadgeType[key]}`]}
                </Radio>
              ))}
            </Radio.Group>
          </FormItem>
          <FormItem label='勋章图片' required field='image' rules={[{ required: true }]} help='请上传 png 格式，大小应为 240px * 240px'>
            <Upload
              fileList={file ? [file] : []}
              showUploadList={false}
              accept='.png'
              onChange={(_, currentFile) => {
                setFile({
                  ...currentFile,
                  url: URL.createObjectURL(currentFile.originFile),
                });
              }}
              onProgress={(currentFile) => {
                setFile(currentFile);
              }}
            >
              <div className={cs}>
                {file && file.url ? (
                  <div className='arco-upload-list-item-picture custom-upload-avatar'>
                    <img src={file.url} />
                    <div className='arco-upload-list-item-picture-mask'>
                      <IconEdit />
                    </div>
                    {file.status === 'uploading' && file.percent < 100 && (
                      <Progress
                        percent={file.percent}
                        type='circle'
                        size='mini'
                        style={{
                          position: 'absolute',
                          left: '50%',
                          top: '50%',
                          transform: 'translateX(-50%) translateY(-50%)',
                        }}
                      />
                    )}
                  </div>
                ) : (
                  <div className='arco-upload-trigger-picture'>
                    <div className='arco-upload-trigger-picture-text'>
                      <IconPlus />
                      <div style={{ marginTop: 10, fontWeight: 600 }}>选择</div>
                    </div>
                  </div>
                )}
              </div>
            </Upload>
          </FormItem>
          <FormItem label='勋章动态图片' required field='imageGif' rules={[{ required: true }]} help='请上传 gif 格式，大小应为 360px * 360px'>
            <Upload
              fileList={fileGif ? [fileGif] : []}
              accept='.gif'
              showUploadList={false}
              onChange={(_, currentFile) => {
                setFileGif({
                  ...currentFile,
                  url: URL.createObjectURL(currentFile.originFile),
                });
              }}
              onProgress={(currentFile) => {
                setFileGif(currentFile);
              }}
            >
              <div className={cs}>
                {fileGif && fileGif.url ? (
                  <div className='arco-upload-list-item-picture custom-upload-avatar'>
                    <img src={fileGif.url} />
                    <div className='arco-upload-list-item-picture-mask'>
                      <IconEdit />
                    </div>
                    {fileGif.status === 'uploading' && fileGif.percent < 100 && (
                      <Progress
                        percent={fileGif.percent}
                        type='circle'
                        size='mini'
                        style={{
                          position: 'absolute',
                          left: '50%',
                          top: '50%',
                          transform: 'translateX(-50%) translateY(-50%)',
                        }}
                      />
                    )}
                  </div>
                ) : (
                  <div className='arco-upload-trigger-picture'>
                    <div className='arco-upload-trigger-picture-text'>
                      <IconPlus />
                      <div style={{ marginTop: 10, fontWeight: 600 }}>选择</div>
                    </div>
                  </div>
                )}
              </div>
            </Upload>
          </FormItem>
        </Form>
      </Modal>
    </div>
  );
}

function UpdateModal({id, visible, setVisible, callback}: {id: number, visible: boolean, setVisible: any, callback: () => void}) {
  const t = useLocale(locale);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const [file, setFile] = React.useState<UploadItem>();
  const [fileGif, setFileGif] = React.useState<UploadItem>();
  const cs = `arco-upload-list-item${file && file.status === 'error' ? ' is-error' : ''}`;

  function onOk() {
    form.validate().then((values) => {
      const formData = new FormData();
      formData.append('name', values.name);
      formData.append('type', values.type);
      formData.append('image', values.image[0].originFile);
      formData.append('imageGif', values.imageGif[0].originFile);
      setConfirmLoading(true);
      updateUserBadge(id, values)
        .then(res => {
          Message.info('修改成功');
          setVisible(false);
        })
        .finally(() => {
          setConfirmLoading(false);
          callback();
        });
    });
  }

  useEffect(() => {
    if (visible) {
      getUserBadge(id).then(res => {
        form.setFieldsValue({
          name: res.data.name,
          type: res.data.type,
        });
      });
    }
  }, [visible]);

  return (
    <Modal
      title='修改勋章'
      visible={visible}
      onOk={onOk}
      confirmLoading={confirmLoading}
      onCancel={() => setVisible(false)}
      style={{width: 800}}
    >
      <Form
        form={form}
      >
        <FormItem label='名称' required field='name' rules={[{ required: true }]}>
          <Input placeholder='' />
        </FormItem>
        <FormItem label='类型' required field='type' rules={[{ required: true }]}>
          <Radio.Group
            type='button'
          >
            {Object.keys(UserBadgeType).map(key => (
              <Radio key={key} value={UserBadgeType[key]}>
                {t[`user.form.user.badge.type.${UserBadgeType[key]}`]}
              </Radio>
            ))}
          </Radio.Group>
        </FormItem>
        <FormItem label='勋章图片' required field='image' rules={[{ required: true }]} help='请上传 png 格式，大小应为 240px * 240px'>
          <Upload
            fileList={file ? [file] : []}
            showUploadList={false}
            accept='.png'
            onChange={(_, currentFile) => {
              setFile({
                ...currentFile,
                url: URL.createObjectURL(currentFile.originFile),
              });
            }}
            onProgress={(currentFile) => {
              setFile(currentFile);
            }}
          >
            <div className={cs}>
              {file && file.url ? (
                <div className='arco-upload-list-item-picture custom-upload-avatar'>
                  <img src={file.url} />
                  <div className='arco-upload-list-item-picture-mask'>
                    <IconEdit />
                  </div>
                  {file.status === 'uploading' && file.percent < 100 && (
                    <Progress
                      percent={file.percent}
                      type='circle'
                      size='mini'
                      style={{
                        position: 'absolute',
                        left: '50%',
                        top: '50%',
                        transform: 'translateX(-50%) translateY(-50%)',
                      }}
                    />
                  )}
                </div>
              ) : (
                <div className='arco-upload-trigger-picture'>
                  <div className='arco-upload-trigger-picture-text'>
                    <IconPlus />
                    <div style={{ marginTop: 10, fontWeight: 600 }}>选择</div>
                  </div>
                </div>
              )}
            </div>
          </Upload>
        </FormItem>
        <FormItem label='勋章动态图片' required field='imageGif' rules={[{ required: true }]} help='请上传 gif 格式，大小应为 360px * 360px'>
          <Upload
            fileList={fileGif ? [fileGif] : []}
            accept='.gif'
            showUploadList={false}
            onChange={(_, currentFile) => {
              setFileGif({
                ...currentFile,
                url: URL.createObjectURL(currentFile.originFile),
              });
            }}
            onProgress={(currentFile) => {
              setFileGif(currentFile);
            }}
          >
            <div className={cs}>
              {fileGif && fileGif.url ? (
                <div className='arco-upload-list-item-picture custom-upload-avatar'>
                  <img src={fileGif.url} />
                  <div className='arco-upload-list-item-picture-mask'>
                    <IconEdit />
                  </div>
                  {fileGif.status === 'uploading' && fileGif.percent < 100 && (
                    <Progress
                      percent={fileGif.percent}
                      type='circle'
                      size='mini'
                      style={{
                        position: 'absolute',
                        left: '50%',
                        top: '50%',
                        transform: 'translateX(-50%) translateY(-50%)',
                      }}
                    />
                  )}
                </div>
              ) : (
                <div className='arco-upload-trigger-picture'>
                  <div className='arco-upload-trigger-picture-text'>
                    <IconPlus />
                    <div style={{ marginTop: 10, fontWeight: 600 }}>选择</div>
                  </div>
                </div>
              )}
            </div>
          </Upload>
        </FormItem>
      </Form>
    </Modal>
  );
}

export {UpdateModal, CreateModal};

export default () => {};
