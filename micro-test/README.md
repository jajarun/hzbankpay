# MicroTest Service

This is the MicroTest service

Generated with

```
micro new micro-test
```

## Usage

Generate the proto code

```
make proto
```

Run the service

```
micro run .
```

为什么要把 micro/micro 换成 asim/go-micro

asim/go-micro 比 micro/micro更加完整，多了很多我们开发所用的插件还有方法
例如 asim/go-micro 支持consul，而micro/micro 因为是个云平台，不需要你去考虑注册问题